export function renderWhatsAppButton() {
  const exists = document.getElementById('whatsapp-float');
  if (exists) return;

  const btn = document.createElement('a');
  btn.id = 'whatsapp-float';
  btn.className = 'whatsapp-float';
  btn.href = 'https://wa.me/50588983096';
  btn.target = '_blank';
  btn.rel = 'noopener';
  btn.setAttribute('aria-label', 'WhatsApp');
  btn.innerHTML = '<i class="fab fa-whatsapp"></i>';

  document.body.appendChild(btn);
}