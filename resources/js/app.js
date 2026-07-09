/**
 * REMIS — client-side SPA navigator
 *
 * Intercepts same-origin GET link clicks, fetches the target page,
 * and swaps only #main-content + #page-scripts without a full reload.
 * Handles browser Back/Forward, sidebar active states, Chart.js cleanup,
 * and a slim top-of-page loading bar.
 */

import Chart from 'chart.js/auto';
window.Chart = Chart;

(function () {
    'use strict';

    // ─── DOM anchors (set once; survive all navigations) ─────────────────────
    const progress = document.getElementById('nav-progress');

    // ─── Loading bar ─────────────────────────────────────────────────────────
    function startProgress() {
        if (!progress) return;
        progress.style.transform = 'scaleX(0.6)';
        progress.classList.add('nav-progress--active');
    }
    function finishProgress() {
        if (!progress) return;
        progress.style.transform = 'scaleX(1)';
        setTimeout(() => progress.classList.remove('nav-progress--active'), 250);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Returns the navigable <a> ancestor of el, or null. */
    function navigableAnchor(el) {
        const a = el.closest('a[href]');
        if (!a) return null;
        const href = a.getAttribute('href') || '';

        // Skip non-GET / non-navigable patterns
        if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) return null;
        if (a.hasAttribute('download') || a.target === '_blank') return null;
        // Skip logout and intentional full-page anchors
        if (a.dataset.noAjax !== undefined || a.href.includes('/logout')) return null;

        try {
            const url = new URL(a.href, location.origin);
            if (url.origin !== location.origin) return null;
        } catch { return null; }

        return a;
    }

    /** Destroy any Chart.js instances on canvases about to be removed. */
    function destroyCharts(container) {
        if (!window.Chart) return;
        container.querySelectorAll('canvas').forEach(canvas => {
            const chart = Chart.getChart(canvas);
            if (chart) chart.destroy();
        });
    }

    /** Re-execute inline <script> elements already inside a container (e.g. inside main-content). */
    function runScripts(container) {
        container.querySelectorAll('script').forEach(orig => {
            const clone = document.createElement('script');
            [...orig.attributes].forEach(attr => clone.setAttribute(attr.name, attr.value));
            clone.textContent = orig.textContent;
            orig.replaceWith(clone);
        });
    }

    /**
     * Load external scripts first (sequentially, awaited), then append inline scripts.
     * This guarantees CDN libraries are ready before initialization code runs.
     */
    async function runPageScripts(source, target) {
        const all = [...source.querySelectorAll('script')];
        const external = all.filter(s => s.src || s.getAttribute('src'));
        const inline   = all.filter(s => !(s.src || s.getAttribute('src')) && s.textContent.trim());

        // Load each external script in sequence
        for (const orig of external) {
            await new Promise((resolve) => {
                const clone = document.createElement('script');
                [...orig.attributes].forEach(a => clone.setAttribute(a.name, a.value));
                clone.onload  = resolve;
                clone.onerror = resolve; // don't block on CDN error
                target.appendChild(clone);
            });
        }

        // Then execute inline scripts (CDN libs now guaranteed available)
        for (const orig of inline) {
            const clone = document.createElement('script');
            clone.textContent = orig.textContent;
            target.appendChild(clone);
        }
    }

    /** Sync sidebar nav active classes from the fetched document. */
    function syncSidebar(fetchedDoc) {
        const fetchedLinks  = [...fetchedDoc.querySelectorAll('#sidebar nav a')];
        const currentLinks  = [...document.querySelectorAll('#sidebar nav a')];
        fetchedLinks.forEach((fl, i) => {
            if (currentLinks[i]) currentLinks[i].className = fl.className;
        });
    }

    /** Sync the notification bell badge from the fetched document. */
    function syncBell(fetchedDoc) {
        const fetched = fetchedDoc.querySelector('[aria-label="Upcoming Payments"]');
        const current = document.querySelector('[aria-label="Upcoming Payments"]');
        if (fetched && current) current.innerHTML = fetched.innerHTML;
    }

    // ─── Core navigate function ───────────────────────────────────────────────
    let navigating = false;

    async function navigate(url, pushState = true) {
        if (navigating) return;
        navigating = true;
        startProgress();

        const mainContent = document.getElementById('main-content');
        const pageScripts = document.getElementById('page-scripts');

        // Fade out current content
        if (mainContent) mainContent.classList.add('page-transitioning');

        try {
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Ajax-Nav': '1',
                },
            });

            // Auth redirect (302 → login page) or non-2xx → hard navigate
            if (res.redirected || !res.ok) {
                window.location.href = res.url || url;
                return;
            }

            const html  = await res.text();
            const parser = new DOMParser();
            const doc   = parser.parseFromString(html, 'text/html');

            const newMain    = doc.getElementById('main-content');
            const newScripts = doc.getElementById('page-scripts');

            if (!newMain || !mainContent) {
                window.location.href = url;
                return;
            }

            // Destroy charts before removing canvases
            destroyCharts(mainContent);

            // ── Swap main content ──
            mainContent.innerHTML = newMain.innerHTML;

            // ── Re-run any inline scripts embedded inside the content itself ──
            runScripts(mainContent);

            // ── Swap and re-execute page scripts (external → inline, in order) ──
            if (pageScripts && newScripts) {
                pageScripts.innerHTML = '';
                await runPageScripts(newScripts, pageScripts);
            }

            // ── Update sidebar active states ──
            syncSidebar(doc);

            // ── Update bell badge ──
            syncBell(doc);

            // ── Update page title ──
            document.title = doc.title;

            // ── Browser history ──
            if (pushState) history.pushState({ url }, doc.title, url);

            // Scroll content area to top
            window.scrollTo({ top: 0, behavior: 'instant' });

        } catch (err) {
            // Network error — fall back to full navigation
            window.location.href = url;
        } finally {
            navigating = false;
            finishProgress();
            if (mainContent) mainContent.classList.remove('page-transitioning');
        }
    }

    // ─── Click interception ───────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        // Ignore modified clicks (new tab, etc.)
        if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (e.button !== 0) return; // only left-click

        const a = navigableAnchor(e.target);
        if (!a) return;

        e.preventDefault();
        navigate(a.href);
    }, true); // capture phase so modal overlays don't swallow it

    // ─── Browser back / forward ───────────────────────────────────────────────
    window.addEventListener('popstate', function (e) {
        const url = (e.state && e.state.url) ? e.state.url : location.href;
        navigate(url, false);
    });

    // Seed the initial history entry so popstate works from page 1
    history.replaceState({ url: location.href }, document.title, location.href);

})();
