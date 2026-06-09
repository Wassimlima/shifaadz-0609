// ── Med-Rep Dashboard ─────────────────────────────────────────────────────────
let _selectedPharmacyId = null;
let _resupplyTarget = null;

async function loadRepDashboard() {
  try {
    const d = await api.get('/med-rep/dashboard.php', { repId: REP_ID });
    // Welcome
    document.getElementById('rep-name').textContent = d.rep?.name || '—';
    document.getElementById('rep-region').textContent = d.rep?.region || '';
    // Stats
    document.getElementById('st-products').textContent  = d.stats.totalProducts;
    document.getElementById('st-partners').textContent  = d.stats.partnerPharmacies;
    document.getElementById('st-alerts').textContent    = d.stats.urgentAlerts;
    // Notification
    if (d.stats.urgentAlerts > 0) {
      document.getElementById('urgent-notif').textContent = d.stats.urgentAlerts + ' تنبيه عاجل';
      document.getElementById('urgent-notif').style.display = 'flex';
    }
    // Tab badges
    if (d.stats.urgentAlerts > 0) {
      ['tab-alerts-badge', 'nav-alerts-badge'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) { el.textContent = d.stats.urgentAlerts; el.style.display = 'flex'; }
      });
    }
    renderProducts(d.products);
    renderAlerts(d.alerts);
    window._lastPartners = d.partnerPharmacies || [];
    renderPartners(window._lastPartners);
  } catch (err) {
    console.error(err);
  }
}

async function loadResupplyCount() {
  try {
    const list = await api.get('/resupply/requests.php', { repId: REP_ID });
    const pending = list.filter(r => r.status === 'pending').length;
    document.getElementById('st-resupply').textContent = pending;
    if (pending > 0) {
      ['tab-resupply-badge', 'nav-resupply-badge'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) { el.textContent = pending; el.style.display = 'flex'; }
      });
    }
    renderResupplyRequests(list);
  } catch {}
}

function renderProducts(products) {
  const el = document.getElementById('products-content');
  if (!products?.length) { el.innerHTML = '<div class="empty-state"><div class="empty-icon">📦</div><p>لا توجد منتجات</p></div>'; return; }
  const stBadge = {
    good:     '<span class="badge badge-secondary">جيد</span>',
    warning:  '<span class="badge badge-orange">تحذير</span>',
    critical: '<span class="badge badge-red">حرج</span>',
  };
  el.innerHTML = `<div class="table-card"><div class="table-wrapper"><table>
    <thead><tr><th>المنتج</th><th>إجمالي المخزون</th><th>صيدليات منخفضة</th><th>الحالة</th></tr></thead>
    <tbody>${products.map(p => {
      const pct = Math.min(100, Math.max(5, (p.total_stock / 500) * 100));
      const color = p.status === 'good' ? 'var(--secondary)' : p.status === 'warning' ? 'var(--orange)' : 'var(--red)';
      return `<tr>
        <td><strong>${p.name}</strong>
          <div class="prod-progress"><div class="prod-progress-fill" style="width:${pct}%;background:${color}"></div></div>
        </td>
        <td><strong style="font-size:1.1rem">${Number(p.total_stock).toLocaleString('ar')}</strong></td>
        <td>${p.low_stock_pharmacies} صيدلية</td>
        <td>${stBadge[p.status] || p.status}</td>
      </tr>`;
    }).join('')}</tbody></table></div></div>`;
}

function renderAlerts(alerts) {
  const el = document.getElementById('alerts-content');
  if (!alerts?.length) { el.innerHTML = '<div class="empty-state"><div class="empty-icon">✅</div><p>لا توجد تنبيهات — المخزون في حالة جيدة</p></div>'; return; }
  el.innerHTML = alerts.map(a => `
    <div class="alert-card ${a.severity}">
      <div>
        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;margin-bottom:0.4rem">
          <span class="badge ${a.severity === 'high' ? 'badge-red' : a.severity === 'medium' ? 'badge-orange' : 'badge-muted'}">${a.severity === 'high' ? 'عالي' : a.severity === 'medium' ? 'متوسط' : 'منخفض'}</span>
          <strong>${a.product_name}</strong>
        </div>
        <div style="font-size:0.82rem;color:var(--muted);display:flex;gap:1rem;flex-wrap:wrap">
          <span>🏥 ${a.pharmacy_name}</span>
          <span><strong style="color:var(--text)">متبقٍّ: ${a.remaining_stock} وحدة</strong></span>
          <span>🕐 ${formatDate(a.created_at)}</span>
        </div>
      </div>
      <div style="display:flex;gap:0.5rem;align-items:flex-start;flex-shrink:0;flex-wrap:wrap">
        ${a.pharmacy_phone ? `<button class="btn btn-ghost btn-sm" onclick="window.open('tel:${a.pharmacy_phone}')">📞 اتصال</button>` : ''}
        <button class="btn btn-sm" style="background:${a.severity==='high'?'var(--red)':'var(--orange)'};color:white"
          onclick="openResupplyModal({pharmacyId:${a.pharmacy_id||1},pharmacyName:'${a.pharmacy_name}',productName:'${a.product_name}',remaining:${a.remaining_stock}})">
          🔄 طلب تموين
        </button>
      </div>
    </div>`).join('');
}

function renderPartners(partners) {
  const el = document.getElementById('partners-content');
  if (!partners?.length) { el.innerHTML = '<div class="empty-state"><div class="empty-icon">🤝</div><p>لا توجد صيدليات شريكة</p></div>'; return; }
  const stMap = {
    accepted: { label:'نشط', cls:'badge-secondary' },
    pending:  { label:'معلق', cls:'badge-orange' },
    rejected: { label:'مرفوض', cls:'badge-red' },
    revoked:  { label:'ملغى', cls:'badge-muted' },
  };
  el.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.75rem">
      <h3 class="section-title" style="margin:0"><span class="icon">🏥</span> الصيدليات الشريكة</h3>
      <button class="btn btn-primary btn-sm" onclick="openModal('new-partner-modal')">+ طلب شراكة جديدة</button>
    </div>
    <div class="grid-2" style="margin-bottom:1.25rem">
      ${partners.map(p => {
        const s = stMap[p.status] || stMap.pending;
        const isSelected = _selectedPharmacyId === (p.pharmacy_id || p.pharmacyId);
        const isAccepted = p.status === 'accepted';
        return `<button class="pharmacy-btn-card ${isSelected ? 'selected' : ''}" ${!isAccepted ? 'disabled' : ''}
          onclick="${isAccepted ? `selectPharmacy(${p.pharmacy_id || 1},'${p.pharmacy_name}')` : ''}">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-weight:700;margin-bottom:0.2rem">${p.pharmacy_name || 'صيدلية'}</div>
              ${p.message ? `<div style="font-size:0.75rem;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${p.message}</div>` : ''}
            </div>
            <div>
              <span class="badge ${s.cls}">${s.label}</span>
              ${isAccepted ? `<div style="font-size:0.72rem;color:var(--primary);margin-top:0.2rem">${isSelected ? '✓ محدد' : 'عرض المخزون ←'}</div>` : ''}
            </div>
          </div>
        </button>`;
      }).join('')}
    </div>
    <div id="filtered-inventory-panel" ${!_selectedPharmacyId ? 'style="display:none"' : ''}></div>`;
}

async function selectPharmacy(pharmacyId, pharmacyName) {
  _selectedPharmacyId = pharmacyId;
  renderPartners(window._lastPartners || []);
  const panel = document.getElementById('filtered-inventory-panel');
  panel.style.display = 'block';
  panel.innerHTML = '<div class="table-card"><div style="padding:1rem;text-align:center"><div class="spinner"></div></div></div>';
  try {
    const data = await api.get('/partnerships/filtered-inventory.php', { repId: REP_ID, pharmacyId });
    const stBadge = {
      available:   '<span class="badge badge-secondary">متوفر</span>',
      limited:     '<span class="badge badge-orange">محدود</span>',
      unavailable: '<span class="badge badge-red">غير متوفر</span>',
    };
    panel.innerHTML = `
      <div class="table-card">
        <div class="inv-filter-header">
          <span>🛡️ مخزون منتجاتي فقط — ${pharmacyName}</span>
          <button onclick="closeFilteredInventory()" style="background:none;border:none;cursor:pointer;color:var(--primary)">✕</button>
        </div>
        ${!data.items?.length ? '<div class="empty-state" style="padding:1.5rem"><div class="empty-icon" style="font-size:1.5rem">📦</div><p>لا توجد منتجاتك في هذه الصيدلية</p></div>' :
        `<div class="table-wrapper"><table>
          <thead><tr><th>المنتج</th><th>الكمية</th><th>الحالة</th><th>آخر تحديث</th><th></th></tr></thead>
          <tbody>${data.items.map(item => `<tr>
            <td><strong>${item.product_name}</strong></td>
            <td>${item.quantity}</td>
            <td>${stBadge[item.status] || item.status}</td>
            <td style="font-size:0.78rem;color:var(--muted)">${formatDate(item.last_updated)}</td>
            <td>${item.status !== 'available' ? `<button class="btn btn-sm" style="background:var(--orange);color:white;font-size:0.75rem"
              onclick="openResupplyModal({pharmacyId:${pharmacyId},pharmacyName:'${pharmacyName}',productName:'${item.product_name}',remaining:${item.quantity}})">🔄 تموين</button>` : ''}</td>
          </tr>`).join('')}</tbody>
        </table></div>`}
      </div>`;
  } catch {
    panel.innerHTML = '<div class="empty-state"><p>تعذر تحميل البيانات</p></div>';
  }
}

function closeFilteredInventory() {
  _selectedPharmacyId = null;
  document.getElementById('filtered-inventory-panel').style.display = 'none';
}

function renderResupplyRequests(list) {
  const el = document.getElementById('resupply-content');
  if (!list?.length) { el.innerHTML = '<div class="empty-state"><div class="empty-icon">📤</div><p>لم ترسل أي طلبات تموين بعد</p></div>'; return; }
  const stBadge = {
    pending:   '<span class="badge badge-orange">في الانتظار</span>',
    confirmed: '<span class="badge badge-primary">مؤكد ✓</span>',
    sent:      '<span class="badge badge-secondary">تم الإرسال ✓</span>',
    rejected:  '<span class="badge badge-red">مرفوض</span>',
  };
  el.innerHTML = list.map(req => `
    <div class="resupply-card animate-in">
      <div class="resupply-card-row">
        <div>
          <div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.35rem">${stBadge[req.status]||''}<strong>${req.product_name}</strong></div>
          <div style="font-size:0.82rem;color:var(--muted)">🏥 ${req.pharmacy_name} &nbsp;·&nbsp; الكمية: <strong style="color:var(--text)">${req.requested_quantity}</strong></div>
          ${req.message ? `<div style="font-size:0.8rem;background:var(--muted-bg);border-radius:4px;padding:0.3rem 0.6rem;margin-top:0.3rem">"${req.message}"</div>` : ''}
          <div style="font-size:0.75rem;color:var(--muted);margin-top:0.3rem">🕐 ${formatDate(req.created_at)}</div>
        </div>
      </div>
    </div>`).join('');
}

// ── Resupply modal ──────────────────────────────────────────────────────────
function openResupplyModal({ pharmacyId, pharmacyName, productName, remaining }) {
  _resupplyTarget = { pharmacyId, pharmacyName, productName };
  document.getElementById('rs-product').textContent  = productName;
  document.getElementById('rs-pharmacy').textContent = pharmacyName;
  document.getElementById('rs-remaining').textContent = remaining + ' وحدة';
  document.getElementById('rs-qty').value = 10;
  document.getElementById('rs-message').value = '';
  openModal('resupply-modal');
}

async function submitResupply(e) {
  e.preventDefault();
  if (!_resupplyTarget) return;
  const btn = e.target.querySelector('[type=submit]');
  btn.disabled = true; btn.textContent = 'جارٍ الإرسال...';
  try {
    await api.post('/resupply/create.php', {
      repId: REP_ID,
      pharmacyId: _resupplyTarget.pharmacyId,
      productName: _resupplyTarget.productName,
      requestedQuantity: parseInt(document.getElementById('rs-qty').value) || 1,
      message: document.getElementById('rs-message').value,
    });
    closeModal('resupply-modal');
    showToast('تم إرسال طلب التموين', 'success');
    loadResupplyCount();
  } catch { showToast('حدث خطأ', 'error'); }
  finally { btn.disabled = false; btn.textContent = 'إرسال الطلب'; }
}

// ── New partnership ──────────────────────────────────────────────────────────
async function submitNewPartnership(e) {
  e.preventDefault();
  const btn = e.target.querySelector('[type=submit]');
  btn.disabled = true; btn.textContent = 'جارٍ الإرسال...';
  try {
    await api.post('/med-rep/partnership-request.php', {
      repId: REP_ID,
      pharmacyId: parseInt(document.getElementById('np-pharmacy-id').value),
      message: document.getElementById('np-message').value,
    });
    closeModal('new-partner-modal');
    showToast('تم إرسال طلب الشراكة', 'success');
    e.target.reset();
    loadRepDashboard();
  } catch(err) { showToast(err.message || 'حدث خطأ', 'error'); }
  finally { btn.disabled = false; btn.textContent = 'إرسال الطلب'; }
}

function switchMedRepTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((b) => {
    b.classList.toggle('active', b.dataset.tab === tab);
  });
  document.querySelectorAll('.tab-panel').forEach((p) => p.classList.remove('active'));
  document.getElementById('panel-' + tab)?.classList.add('active');
}

async function loadPharmacyOptions() {
  const sel = document.getElementById('np-pharmacy-id');
  if (!sel) return;
  try {
    const data = await api.get('/pharmacies/index.php', { limit: 50 });
    const list = data.pharmacies || [];
    sel.innerHTML = '<option value="">— اختر صيدلية —</option>' +
      list.map((p) => `<option value="${p.id}">${p.name} · ${p.wilaya || ''}</option>`).join('');
  } catch {
    sel.innerHTML = '<option value="">تعذر تحميل القائمة</option>';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  loadRepDashboard();
  loadResupplyCount();
  loadPharmacyOptions();
  document.getElementById('resupply-form')?.addEventListener('submit', submitResupply);
  document.getElementById('new-partner-form')?.addEventListener('submit', submitNewPartnership);

  document.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => switchMedRepTab(btn.dataset.tab));
  });

  if (typeof initProSidebarNav === 'function') {
    initProSidebarNav({
      'section-overview': () => { window.scrollTo({ top: 0, behavior: 'smooth' }); switchMedRepTab('products'); },
      'section-partners': () => { document.getElementById('panel-partners')?.scrollIntoView({ behavior: 'smooth' }); switchMedRepTab('partners'); },
      'section-resupply': () => { document.getElementById('panel-resupply')?.scrollIntoView({ behavior: 'smooth' }); switchMedRepTab('resupply'); },
      'section-alerts': () => { document.getElementById('panel-alerts')?.scrollIntoView({ behavior: 'smooth' }); switchMedRepTab('alerts'); },
      'section-products': () => { document.getElementById('panel-products')?.scrollIntoView({ behavior: 'smooth' }); switchMedRepTab('products'); },
    });
  }
});
