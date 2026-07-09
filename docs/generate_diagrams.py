"""Generate architecture/ERD/flow diagrams as PNG files for the Word report."""
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch
import os

OUT = r"c:\xampp\htdocs\RemisV2\docs\images"
os.makedirs(OUT, exist_ok=True)

# ── colour palette ──────────────────────────────────────────────────────────
C_BLUE   = "#1e3a5f"
C_LBLUE  = "#2e6da4"
C_TEAL   = "#17a2b8"
C_GREEN  = "#28a745"
C_ORANGE = "#fd7e14"
C_RED    = "#dc3545"
C_GREY   = "#6c757d"
C_LIGHT  = "#f8f9fa"
C_WHITE  = "#ffffff"
C_DARK   = "#343a40"

def box(ax, x, y, w, h, label, color, textcolor="white", fontsize=9, radius=0.03):
    rect = FancyBboxPatch((x-w/2, y-h/2), w, h,
                          boxstyle=f"round,pad=0.01,rounding_size={radius}",
                          facecolor=color, edgecolor="white", linewidth=1.2, zorder=3)
    ax.add_patch(rect)
    ax.text(x, y, label, ha="center", va="center", fontsize=fontsize,
            color=textcolor, fontweight="bold", zorder=4, wrap=True,
            multialignment="center")

def arrow(ax, x1, y1, x2, y2, color="#555555", lw=1.5, style="->"):
    ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                arrowprops=dict(arrowstyle=style, color=color,
                                lw=lw, connectionstyle="arc3,rad=0.0"), zorder=2)

# ═══════════════════════════════════════════════════════════════════════════
# 1. SYSTEM ARCHITECTURE
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(14, 9))
ax.set_xlim(0, 14); ax.set_ylim(0, 9)
ax.axis("off")
fig.patch.set_facecolor(C_LIGHT)
ax.set_facecolor(C_LIGHT)
ax.set_title("REMIS V2 — Backend System Architecture", fontsize=14,
             fontweight="bold", color=C_BLUE, pad=12)

# Browser
box(ax, 7, 8.3, 2.2, 0.7, "Browser / Client", C_BLUE, fontsize=9)

# Middleware stack
mw_labels = ["auth  |  role:landlord/tenant/admin  |  locked  |  force.password.change"]
box(ax, 7, 7.3, 8, 0.6, "Middleware Pipeline", C_LBLUE, fontsize=8.5)

# Controllers row
controllers = [
    (2.2, 6.2, "AuthController\n/login /register\n/lock /unlock"),
    (5.5, 6.2, "LandlordController\nProperties · Leases\nTenants · Reports"),
    (8.8, 6.2, "PaymentController\nNMB Control Numbers\nStatus Polling"),
    (12, 6.2, "AdminController\nSettings · Audit\nBackups · Users"),
]
for cx, cy, lbl in controllers:
    box(ax, cx, cy, 2.6, 1.1, lbl, C_TEAL, fontsize=7.5)

# TenantController separate
box(ax, 7, 5.0, 2.6, 0.7, "TenantController\nDashboard · Payments · Maintenance", C_TEAL, fontsize=7.5)

# Service layer
services = [
    (2.5, 3.6, "LeasePayment\nSyncService"),
    (5.8, 3.6, "NmbPayment\nService"),
    (9.0, 3.6, "BriqSms\nService"),
    (11.8, 3.6, "TinVerification\nController"),
]
for sx, sy, lbl in services:
    box(ax, sx, sy, 2.3, 0.85, lbl, C_ORANGE, fontsize=7.5)

# Models row
models = ["User", "Property", "Unit", "Lease", "Payment", "Maintenance\nRequest", "AuditLog"]
for i, m in enumerate(models):
    box(ax, 1.0 + i*2.0, 2.3, 1.7, 0.7, m, C_GREEN, fontsize=7.5)

# Database
box(ax, 7, 1.1, 5, 0.7, "MySQL Database  (remis_db)", C_DARK, fontsize=9)

# External APIs
ext = [(2.5, 0.3,"NMB Bank SPG\n(Payment Gateway)"),
       (7.0, 0.3,"BRIQ SMS API\n(Tanzania)"),
       (11.5,0.3,"TRA API\n(TIN Verification)")]
for ex, ey, lbl in ext:
    box(ax, ex, ey, 2.8, 0.55, lbl, C_RED, fontsize=7.5)

# Arrows
arrow(ax, 7, 7.97, 7, 7.6)
arrow(ax, 7, 7.0, 7, 6.75)
for cx, cy, _ in controllers:
    arrow(ax, cx, 5.65, cx if cx != 7 else 7, 5.35 if cx == 7 else 4.95)
arrow(ax, 7, 4.65, 7, 4.03)
for sx, sy, _ in services:
    arrow(ax, sx, 3.17, sx, 2.65)
for i in range(len(models)):
    mx = 1.0 + i*2.0
    arrow(ax, mx, 1.96, mx if mx < 6 else (7 + (mx-7)*0.3), 1.44)
for ex, ey, _ in ext:
    arrow(ax, ex, 0.58, ex, 0.88 if ey < 0.9 else ey+0.35)

plt.tight_layout()
plt.savefig(f"{OUT}/architecture.png", dpi=150, bbox_inches="tight",
            facecolor=C_LIGHT)
plt.close()
print("architecture.png saved")

# ═══════════════════════════════════════════════════════════════════════════
# 2. DATABASE ERD
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(16, 10))
ax.set_xlim(0, 16); ax.set_ylim(0, 10)
ax.axis("off")
fig.patch.set_facecolor("#fefefe")
ax.set_title("REMIS V2 — Entity-Relationship Diagram", fontsize=14,
             fontweight="bold", color=C_BLUE, pad=10)

def erd_table(ax, x, y, title, fields, w=2.8, color=C_LBLUE):
    row_h = 0.32
    total_h = row_h * (len(fields) + 1)
    # header
    hdr = FancyBboxPatch((x, y - row_h), w, row_h,
                         boxstyle="square,pad=0", facecolor=color,
                         edgecolor="#aaa", linewidth=1, zorder=3)
    ax.add_patch(hdr)
    ax.text(x + w/2, y - row_h/2, title, ha="center", va="center",
            fontsize=8, fontweight="bold", color="white", zorder=4)
    # rows
    for i, fld in enumerate(fields):
        bg = "#e8f0fe" if i % 2 == 0 else "#f8f9ff"
        r = FancyBboxPatch((x, y - row_h*(i+2)), w, row_h,
                           boxstyle="square,pad=0", facecolor=bg,
                           edgecolor="#ccc", linewidth=0.5, zorder=3)
        ax.add_patch(r)
        is_pk = fld.startswith("PK")
        is_fk = fld.startswith("FK")
        fc = "#b8860b" if is_pk else ("#8b0000" if is_fk else C_DARK)
        ax.text(x + 0.1, y - row_h*(i+1.5), fld, ha="left", va="center",
                fontsize=6.5, color=fc, zorder=4, family="monospace")
    return (x + w/2, y - row_h/2), (x + w/2, y - total_h + row_h/2)

tables = {
    "users": (0.3, 9.6, ["PK id", "name", "email", "phone", "role",
                          "FK landlord_id", "tenant_status", "tin",
                          "nida_number", "force_password_change",
                          "profile_picture", "preferences (JSON)"]),
    "properties": (4.0, 9.6, ["PK id", "FK landlord_id", "name", "address",
                               "city", "county", "type", "status",
                               "property_category", "floor_layout", "image"]),
    "units": (8.0, 9.6, ["PK id", "FK property_id", "unit_number",
                          "floor_number", "status", "notes"]),
    "leases": (11.8, 9.6, ["PK id", "FK property_id", "FK unit_id",
                            "FK tenant_id", "FK landlord_id",
                            "start_date", "end_date", "monthly_rent",
                            "security_deposit", "payment_day", "status",
                            "termination_reason", "FK renewal_of_id"]),
    "payments": (4.0, 4.8, ["PK id", "FK lease_id", "FK tenant_id",
                             "amount", "due_date", "paid_date", "status",
                             "control_number", "control_number_generated_at",
                             "nmb_transaction_id", "nmb_receipt_number",
                             "nmb_payer_name", "nmb_payer_mobile"]),
    "maintenance_requests": (0.3, 4.8, ["PK id", "FK property_id",
                                         "FK tenant_id", "title",
                                         "description", "priority", "status",
                                         "due_date"]),
    "audit_logs": (8.0, 4.8, ["PK id", "FK user_id", "action",
                               "model_type", "model_id", "description",
                               "ip_address", "user_agent"]),
    "system_settings": (11.8, 4.8, ["PK id", "key (unique)", "label",
                                     "description", "value", "type"]),
}

tops = {}
bots = {}
for name, (x, y, fields) in tables.items():
    t, b = erd_table(ax, x, y, name, fields,
                     color=C_BLUE if name=="users" else C_LBLUE)
    tops[name] = t
    bots[name] = b

# Relationship lines
rels = [
    ("users", "properties", "1:N  landlord"),
    ("users", "leases",     "1:N  as landlord"),
    ("users", "leases",     "1:N  as tenant"),
    ("properties", "units", "1:N"),
    ("properties", "leases","1:N"),
    ("units", "leases",     "1:N"),
    ("leases", "payments",  "1:N"),
    ("users", "payments",   "1:N  tenant"),
    ("properties","maintenance_requests","1:N"),
    ("users","maintenance_requests","1:N  tenant"),
    ("users","audit_logs","1:N"),
]
for src, dst, lbl in rels:
    x1, y1 = bots[src]
    x2, y2 = tops[dst]
    ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                arrowprops=dict(arrowstyle="-|>", color="#666",
                                lw=1.0, connectionstyle="arc3,rad=0.15"),
                zorder=2)
    mx, my = (x1+x2)/2, (y1+y2)/2
    ax.text(mx, my, lbl, fontsize=5.5, color="#444", ha="center",
            bbox=dict(fc="white", ec="none", pad=1), zorder=5)

plt.tight_layout()
plt.savefig(f"{OUT}/erd.png", dpi=150, bbox_inches="tight", facecolor="white")
plt.close()
print("erd.png saved")

# ═══════════════════════════════════════════════════════════════════════════
# 3. REQUEST LIFECYCLE / MIDDLEWARE PIPELINE
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(12, 7))
ax.set_xlim(0, 12); ax.set_ylim(0, 7)
ax.axis("off")
fig.patch.set_facecolor(C_LIGHT)
ax.set_title("HTTP Request Lifecycle & Middleware Pipeline", fontsize=13,
             fontweight="bold", color=C_BLUE, pad=10)

steps = [
    (6, 6.4, 3.5, 0.6, "Incoming HTTP Request", C_BLUE),
    (6, 5.6, 3.5, 0.6, "Route Matching  (web.php)", C_LBLUE),
    (2.5,4.6, 2.5,0.6,  "auth\n(Authenticate)", C_TEAL),
    (5.5,4.6, 2.5,0.6,  "role:X\n(EnsureRole)", C_TEAL),
    (8.5,4.6, 2.5,0.6,  "locked\n(CheckSessionLock)", C_TEAL),
    (6, 3.6, 3.5, 0.6, "force.password.change\n(tenants only)", C_ORANGE),
    (6, 2.7, 3.5, 0.6, "Controller Method", C_GREEN),
    (3, 1.8, 2.4, 0.6, "Service Layer\nBusiness Logic", C_ORANGE),
    (6, 1.8, 2.4, 0.6, "Eloquent Models\n/ Database", C_LBLUE),
    (9, 1.8, 2.4, 0.6, "External APIs\nNMB / BRIQ / TRA", C_RED),
    (6, 0.7, 3.5, 0.6, "HTTP Response  (View / JSON / PDF)", C_BLUE),
]
for (x, y, w, h, lbl, col) in steps:
    box(ax, x, y, w, h, lbl, col, fontsize=8)

# vertical arrows
arrow(ax, 6, 6.1, 6, 5.9)
arrow(ax, 6, 5.3, 6, 4.9)
arrow(ax, 6, 5.3, 2.5, 4.9)
arrow(ax, 6, 5.3, 8.5, 4.9)
for x_ in [2.5, 5.5, 8.5]:
    arrow(ax, x_, 4.3, 6, 3.9)
arrow(ax, 6, 3.3, 6, 3.0)
arrow(ax, 6, 2.4, 3, 2.1)
arrow(ax, 6, 2.4, 6, 2.1)
arrow(ax, 6, 2.4, 9, 2.1)
for x_ in [3, 6, 9]:
    arrow(ax, x_, 1.5, 6, 1.0)

# 403 abort labels
ax.text(10.2, 4.6, "403 if\nunauthorized", fontsize=7, color=C_RED,
        ha="left", va="center")

plt.tight_layout()
plt.savefig(f"{OUT}/request_lifecycle.png", dpi=150, bbox_inches="tight",
            facecolor=C_LIGHT)
plt.close()
print("request_lifecycle.png saved")

# ═══════════════════════════════════════════════════════════════════════════
# 4. PAYMENT FLOW DIAGRAM
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(14, 8))
ax.set_xlim(0, 14); ax.set_ylim(0, 8)
ax.axis("off")
fig.patch.set_facecolor(C_LIGHT)
ax.set_title("NMB Rent Payment Flow", fontsize=13,
             fontweight="bold", color=C_BLUE, pad=10)

steps2 = [
    # (x, y, w, h, label, color)
    (2,  7.3, 3, 0.65, "1. LeasePaymentSyncService\nAuto-generates Payment record", C_TEAL),
    (7,  7.3, 3, 0.65, "2. Landlord views\nPayments dashboard", C_LBLUE),
    (11.5,7.3, 2.2,0.65,"3. Landlord clicks\nGenerate Control No.", C_LBLUE),

    (11.5,5.9, 2.2,0.65,"4. NmbPaymentService\ngetToken() → cached", C_ORANGE),
    (11.5,4.8, 2.2,0.65,"5. POST /api/v1/generatectlno\nNMB Bank SPG", C_RED),
    (11.5,3.7, 2.2,0.65,"6. control_number saved\nto payments table", C_GREEN),

    (7,  5.9, 3, 0.65, "7. Send via Email\n(PaymentControlNumber)", C_TEAL),
    (3.5,5.9, 3, 0.65, "7b. Send via SMS\n(BriqSmsService)", C_TEAL),

    (7,  4.5, 3, 0.65, "8. Tenant pays at\nNMB Bank / ATM / App", C_BLUE),

    (7,  3.2, 3, 0.65, "9. PaymentController\ncheckStatus() / pollAll()", C_LBLUE),
    (11.5,2.6,2.2,0.65, "10. GET /api/v1/getpayment\nNMB returns paid=true", C_RED),
    (7,  1.8, 3, 0.65, "11. Payment updated:\nstatus=paid, receipt, payer", C_GREEN),
    (2,  1.8, 3, 0.65, "12. Dashboard shows\nconfirmed payment", C_BLUE),
]
for (x, y, w, h, lbl, col) in steps2:
    box(ax, x, y, w, h, lbl, col, fontsize=7.5)

arrows2 = [
    (2,7.0, 7,7.0), (7,7.0, 11.5,7.0),
    (11.5,7.0, 11.5,6.22),
    (11.5,5.57, 11.5,5.12),
    (11.5,4.47, 11.5,4.02),
    (11.5,3.37, 7,6.22), (11.5,3.37, 3.5,6.22),
    (7,5.57, 7,4.82),
    (7,4.17, 7,3.52),
    (7,2.87, 11.5,2.93),
    (11.5,2.27, 7,2.12),
    (7,1.47, 2,1.47),
]
for (x1,y1,x2,y2) in arrows2:
    arrow(ax, x1, y1, x2, y2, color="#444")

plt.tight_layout()
plt.savefig(f"{OUT}/payment_flow.png", dpi=150, bbox_inches="tight",
            facecolor=C_LIGHT)
plt.close()
print("payment_flow.png saved")

# ═══════════════════════════════════════════════════════════════════════════
# 5. ROLE-BASED AUTH FLOW
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(12, 6))
ax.set_xlim(0, 12); ax.set_ylim(0, 6)
ax.axis("off")
fig.patch.set_facecolor(C_LIGHT)
ax.set_title("Role-Based Authentication & Authorization Flow", fontsize=13,
             fontweight="bold", color=C_BLUE, pad=10)

box(ax,6,5.4,3,0.65,"POST /login  (AuthController::login)",C_BLUE,fontsize=8.5)
box(ax,6,4.5,3,0.65,"Auth::attempt(email, password, rememberMe=true)",C_LBLUE,fontsize=8)
box(ax,2,3.4,2.8,0.65,"FAIL → back()\nwithErrors()",C_RED,fontsize=8)
box(ax,6,3.4,2.8,0.65,"session()->regenerate()\n(prevent fixation)",C_GREEN,fontsize=8)
box(ax,10,3.4,2.8,0.65,"viaRemember = true?\n→ Lock Screen",C_ORANGE,fontsize=8)

box(ax,2,2.1,2.5,0.65,"Admin Dashboard\n/admin/dashboard",C_BLUE,fontsize=8)
box(ax,6,2.1,2.5,0.65,"Landlord Dashboard\n/landlord/dashboard",C_TEAL,fontsize=8)
box(ax,10,2.1,2.5,0.65,"Tenant Dashboard\n/tenant/dashboard",C_GREEN,fontsize=8)

box(ax,6,0.7,6,0.65,"EnsureRole Middleware: abort(403) if role mismatch on any protected route",C_DARK,fontsize=8)

arrow(ax,6,5.07,6,4.82)
arrow(ax,6,4.17,2,3.73)
arrow(ax,6,4.17,6,3.73)
arrow(ax,6,4.17,10,3.73)
for x_ in [2,6,10]:
    arrow(ax,x_,3.07,x_,2.42)
arrow(ax,6,1.77,6,1.02)
ax.text(0.3,4.4,"Invalid\ncredentials",fontsize=7.5,color=C_RED)

plt.tight_layout()
plt.savefig(f"{OUT}/auth_flow.png", dpi=150, bbox_inches="tight",
            facecolor=C_LIGHT)
plt.close()
print("auth_flow.png saved")

# ═══════════════════════════════════════════════════════════════════════════
# 6. LEASE LIFECYCLE
# ═══════════════════════════════════════════════════════════════════════════
fig, ax = plt.subplots(figsize=(12, 5))
ax.set_xlim(0,12); ax.set_ylim(0,5)
ax.axis("off")
fig.patch.set_facecolor(C_LIGHT)
ax.set_title("Lease Lifecycle State Machine", fontsize=13,
             fontweight="bold", color=C_BLUE, pad=10)

states = [
    (1.5,3.2,"Unit Created\n(vacant)",C_GREY),
    (4,3.2,"Lease Created\n(no tenant)",C_LBLUE),
    (6.8,3.2,"Tenant Assigned\n(active)",C_GREEN),
    (9.8,4.2,"Terminated\n(terminated)",C_RED),
    (9.8,2.2,"Renewed\n(renewed → new active)",C_TEAL),
    (6.8,1.2,"Expired\n(expired)",C_ORANGE),
]
for (x,y,lbl,col) in states:
    box(ax,x,y,2.2,0.75,lbl,col,fontsize=8)

arrow(ax,2.6,3.2,2.9,3.2)
arrow(ax,5.1,3.2,5.7,3.2)
arrow(ax,7.9,3.5,8.7,3.85)
arrow(ax,7.9,2.9,8.7,2.55)
arrow(ax,6.8,2.82,6.8,1.58)
ax.text(3.5,3.35,"Create lease",fontsize=7,color=C_DARK,ha="center")
ax.text(5.9,3.35,"Assign tenant",fontsize=7,color=C_DARK,ha="center")
ax.text(8.5,3.8,"Terminate",fontsize=7,color=C_RED)
ax.text(8.5,2.3,"Renew",fontsize=7,color=C_TEAL)
ax.text(7.1,2.0,"end_date\npasses",fontsize=7,color=C_ORANGE)

# payment status sub-diagram
sub = [(1.5,1.1,"pending",C_LBLUE),(4,1.1,"overdue",C_ORANGE),(6.5,1.1,"paid",C_GREEN)]
for (x,y,lbl,col) in sub:
    box(ax,x,y,1.6,0.55,lbl,col,fontsize=8)
arrow(ax,2.3,1.1,3.2,1.1)
arrow(ax,4.8,1.1,5.7,1.1)
arrow(ax,2.3,1.1,5.7,1.1)
ax.text(2.75,1.2,"due_date\npast",fontsize=6.5,ha="center",color=C_DARK)
ax.text(5.3,1.25,"NMB paid=true",fontsize=6.5,ha="center",color=C_DARK)
ax.text(3,0.5,"Payment Status Flow",fontsize=8,fontweight="bold",color=C_DARK,ha="center")

plt.tight_layout()
plt.savefig(f"{OUT}/lease_lifecycle.png", dpi=150, bbox_inches="tight",
            facecolor=C_LIGHT)
plt.close()
print("lease_lifecycle.png saved")

print("\nAll diagrams saved to:", OUT)
