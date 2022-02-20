$(function () {
    // ================================================
    //  HERO SLIDER
    // ================================================
    $('.hero-slider').owlCarousel({
        items: 1,
        nav: true,
        dots: true,
        animateOut: 'fadeOut',
        navText: ["<i class='fas fa-angle-left'></i>","<i class='fas fa-angle-right'></i>"],
        loop: true
    });

    // ================================================
    //  TESTIMONIALS SLIDER
    // ================================================
    $('.testimonials-slider').owlCarousel({
        items: 1,
        margin: 30,
        dots: true,
        responsive: {
            991: {
                items: 2
            }
        }
    });

    // ================================================
    //  MODAL VIDEO
    // ================================================
    new ModalVideo('.video-btn');
});
