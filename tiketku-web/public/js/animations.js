(() => {
  if (!window.gsap || matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  const pageContent = document.querySelectorAll(
    "main.container > h1, main.container > .filters, main.container > .card, " +
      "main.container > .grid, main.container > .layout-2"
  );
  const cards = document.querySelectorAll(".event-card, tbody tr");
  const seats = document.querySelectorAll(".seat.available");

  gsap.from(pageContent, {
    opacity: 0,
    y: 16,
    duration: 0.45,
    stagger: 0.06,
    ease: "power2.out",
    clearProps: "transform",
  });

  gsap.from(cards, {
    opacity: 0,
    y: 14,
    duration: 0.4,
    stagger: 0.05,
    delay: 0.12,
    ease: "power2.out",
    clearProps: "transform",
  });

  cards.forEach((card) => {
    card.addEventListener("pointerenter", () => {
      gsap.to(card, { y: -4, duration: 0.2, ease: "power2.out" });
    });
    card.addEventListener("pointerleave", () => {
      gsap.to(card, { y: 0, duration: 0.2, ease: "power2.out" });
    });
  });

  seats.forEach((seat) => {
    seat.addEventListener("click", () => {
      const selected = seat.classList.contains("selected");
      gsap.fromTo(
        seat,
        { scale: selected ? 1 : 0.94 },
        { scale: selected ? 1.08 : 1, duration: 0.2, ease: "back.out(2)" }
      );
    });
  });
})();
