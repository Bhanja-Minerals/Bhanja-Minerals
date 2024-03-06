(function ($, Drupal, drupalSettings) {
  function basicSlider(element = '#basic-slider', auto = 0, pause) {

    // Get parent element
    var $this = $(element);

    // Slides container
    var slidesCont = $this.children('.bs-slides-container');
    // Get all slides
    var slides = slidesCont.children('.bs-slide');

    // Get Previous / Next links
    var arrowsCont = $this.children('.bs-arrows');
    var prevSlide = arrowsCont.children('.bs-prev');
    var nextSlide = arrowsCont.children('.bs-next');

    // Total slides count
    var slidesCount = slides.length;

    // Set currentSlide to first child
    var currentSlide = slides.first();
    var currentSlideIndex = 1;

    var autoPlay = 0;

    var sliderGo = 0;
    if(auto == 1){
      sliderGo = 1;
    }

    // Hide all slides except first and add active class to first
    slides.not(':first').css('display', 'none');
    currentSlide.addClass('active');

    // Function responsible for fading to next slide
    function fadeNext() {
        currentSlide.removeClass('active').fadeOut(700);

        if(currentSlideIndex == slidesCount) {
            currentSlide = slides.first();
            currentSlide.delay(500).addClass('active').fadeIn(700);
            currentSlideIndex = 1;
        } else {
            currentSlideIndex++;
            currentSlide = currentSlide.next();
            currentSlide.delay(500).addClass('active').fadeIn(700);
        }
    }

    // Function responsible for fading to previous slide
    function fadePrev() {
        currentSlide.removeClass('active').fadeOut(700);

        if(currentSlideIndex == 1) {
            currentSlide = slides.last();
            currentSlide.delay(500).addClass('active').fadeIn();
            currentSlideIndex = slidesCount;
        } else {
            currentSlideIndex--;
            currentSlide = currentSlide.prev();
            currentSlide.delay(500).addClass('active').fadeIn(700);
        }
    }

    // Function that starts the autoplay and resets it in case user navigated (clicked prev or next)
    function AutoPlay() {
        clearInterval(autoPlay);
        if(sliderGo == 1) {
            autoPlay = setInterval(function () {fadeNext()}, pause);
        }
    }

    // Detect if user clicked on arrow for next slide and fade next slide if it did
    $(nextSlide).click(function (e) {
        e.preventDefault();
        fadeNext();
        AutoPlay();
    });

    // Detect if user clicked on arrow for previous slide and fade previous slide if it did
    $(prevSlide).click(function (e) {
        e.preventDefault();
        fadePrev();
        AutoPlay();
    });

    $this.mouseenter(
      function () {
        sliderGo = 0;
        AutoPlay();
      }
    ).mouseleave(
      function () {
        if(auto == 1){
          sliderGo = 1;
          AutoPlay();
        }
      }
    );

    // Start autoplay if auto is set to 1
    AutoPlay();

  }
  basicSlider('#basic-slider', 1, 8000);
})(jQuery, Drupal, drupalSettings);
