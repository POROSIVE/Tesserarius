// it works and you don't change things that work
document.addEventListener("DOMContentLoaded", function() {
  const borderAnimate = document.getElementById('borderanimate')
  if (borderAnimate) {
    setTimeout(() => {
      borderAnimate.style.animation = "none";
      void borderAnimate.offsetWidth;
      borderAnimate.style.animation = "ReverseTimer 4.5s linear forwards";
    }, 500);
  }
})

function alerter(content) {
  if (!content) return;
  const alertcard = document.getElementById('alertcard');
    const alertcontent = document.getElementById('alertcontent');
    if (!alertcard || !alertcontent) return;
    alertcontent.textContent = content;
    setTimeout(() => {
        alertcard.style.transform = "translateX(0)";
    }, 100);
    setTimeout(() => {
        alertcard.style.transform = "translateX(105vw)";
    }, 4500);
}