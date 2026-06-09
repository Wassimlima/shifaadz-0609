/**
 * Single source of truth for role → dashboard URLs.
 * Never fall back to admin — unknown roles go to login/home.
 */
const ROLE_DASHBOARDS = {
  admin: '/shifaa_dizad/frontend/pages/admin/dashboard.html',
  pharmacist: '/shifaa_dizad/frontend/pages/professional/pharmacy-dashboard.html',
  med_rep: '/shifaa_dizad/frontend/pages/professional/medrep-dashboard.html',
  lab: '/shifaa_dizad/frontend/pages/professional/laboratory-dashboard.html',
  medical_services: '/shifaa_dizad/frontend/pages/professional/medical-services-dashboard.html',
};

/** subscription role_type → users.role */
const ROLE_TYPE_TO_USER = {
  pharmacy: 'pharmacist',
  med_rep: 'med_rep',
  lab: 'lab',
  medical_services: 'medical_services',
};

function getDashboardUrl(role) {
  if (!role) return '/shifaa_dizad/frontend/pages/login.html';
  const mapped = ROLE_TYPE_TO_USER[role] || role;
  return ROLE_DASHBOARDS[mapped] || '/shifaa_dizad/frontend/pages/login.html';
}

function redirectAfterAuth(userOrRole, delayMs = 700) {
  const role = typeof userOrRole === 'string' ? userOrRole : userOrRole?.role;
  const url = getDashboardUrl(role);
  if (delayMs > 0) {
    setTimeout(() => { window.location.href = url; }, delayMs);
  } else {
    window.location.replace(url);
  }
  return url;
}

function resolveLoginRedirect(user, queryRedirect) {
  const roleUrl = getDashboardUrl(user?.role);
  if (!queryRedirect) return roleUrl;
  const r = String(queryRedirect).trim();
  if (!r) return roleUrl;
  if (r.startsWith('/shifaa_dizad/')) {
    const adminPath = ROLE_DASHBOARDS.admin;
    if (r === adminPath && user?.role !== 'admin') return roleUrl;
    return r;
  }
  return roleUrl;
}