const avatarInput = document.getElementById("avatar");
const avatarPreview = document.getElementById("avatar-preview");
const avatarFallback = document.getElementById("avatar-fallback");

if (avatarInput && avatarPreview && avatarFallback) {
  let previewUrl;

  avatarInput.addEventListener("change", () => {
    const file = avatarInput.files?.[0];
    if (!file) return;

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    avatarPreview.src = previewUrl;
    avatarPreview.hidden = false;
    avatarFallback.hidden = true;
  });

  window.addEventListener("pagehide", () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
  }, { once: true });
}
