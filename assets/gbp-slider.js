document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.gbp-posts-slider').forEach(function (slider) {
    var autoplay       = slider.dataset.autoplay !== 'false';
    var slidesDesktop  = parseInt(slider.dataset.slidesDesktop, 10) || 3;
    var slidesMid      = Math.min(2, slidesDesktop);

    new Swiper(slider, {
      loop: true,
      spaceBetween: 24,
      grabCursor: true,

      autoplay: autoplay
        ? { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }
        : false,

      pagination: {
        el: slider.querySelector('.swiper-pagination'),
        clickable: true,
        dynamicBullets: true,
      },

      navigation: {
        nextEl: slider.querySelector('.swiper-button-next'),
        prevEl: slider.querySelector('.swiper-button-prev'),
      },

      a11y: {
        prevSlideMessage: 'Previous post',
        nextSlideMessage: 'Next post',
        paginationBulletMessage: 'Go to post {{index}}',
      },

      breakpoints: {
        0:   { slidesPerView: 1 },
        640: { slidesPerView: slidesMid },
        1024:{ slidesPerView: slidesDesktop },
      },
    });
  });
});
