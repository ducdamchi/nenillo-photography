"use strict";

// Selections

const previews = document.querySelectorAll(".img-small");               
const lightbox = document.getElementById("lightbox");
const closeBtn = document.querySelector(".close");
const modal = document.querySelector(".modal-content");

const form = document.getElementById("form");

const backToTopBtn = document.querySelector(".back-to-top");
const rootElement = document.documentElement;
const scrollTarget = document.querySelector(".contact");

const slides_gallery = document.querySelectorAll(".slides img");
let slideIdx = 0;
// let intervalId = null;

// Preventing right-clicking on all <img> elements
document.addEventListener('DOMContentLoaded', () => {
  const imgs = document.querySelectorAll('img');
  imgs.forEach(img => {
      img.addEventListener('contextmenu', (event) => {
          event.preventDefault();
      });
  });
});

document.addEventListener("DOMContentLoaded", initializeSlider);

document.addEventListener('DOMContentLoaded', () => {

  // Select all images with 'data-src' attribute (place-holder for 'src')
  const images = document.querySelectorAll('img[data-src]');

  const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
          // Watch for when an image enters viewport
          if (entry.isIntersecting) {
              const img = entry.target;
              // Set the 'src' attribute, which was empty before to 'data-src' value
              img.src = img.dataset.src; 
              // Remove the data-src attribute
              img.removeAttribute('data-src'); 
              // Stop observing the image
              observer.unobserve(img); 
          }
      });
  });

  images.forEach(img => imageObserver.observe(img));
});


// Add lazy loading to all <img> elements
document.querySelectorAll('img').forEach(img => {
  img.setAttribute('loading', 'lazy');
});



// Functions

const closeModal = function (e) {
  if (e.target !== e.currentTarget) return;
  lightbox.style.display = "none";
};

const scrollToTop = function () {
  rootElement.scrollTo({
    top: 0,
    behavior: "smooth",
  });
};

const callback = function (entries, observer) {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      backToTopBtn.classList.add("showBtn");
    } else {
      backToTopBtn.classList.remove("showBtn");
    }
  });
};

// Lightbox feature
previews.forEach((preview) => {
  preview.addEventListener("click", function (e) {
    lightbox.style.display = "block";
    const modalImg = document.createElement("img");
    modalImg.src = preview.src;

    while (modal.firstChild) {
      modal.removeChild(modal.firstChild);
    }
    modal.appendChild(modalImg);
  });
});

closeBtn.addEventListener("click", closeModal);
lightbox.addEventListener("click", closeModal);

// Prevent submit

form.addEventListener("submit", (e) => e.preventDefault());
// Back to top button

backToTopBtn.addEventListener("click", scrollToTop);

let observer = new IntersectionObserver(callback);
observer.observe(scrollTarget);



/* THIRD ATTEMPT */

/* Note: This works for 3 slides, each slide containing 3 images. 
Needs modification to work with other specs. */

const slider = document.querySelector(".slider");
const slides = document.querySelectorAll(".album");
const btnLeft = document.getElementById("moveLeft");
const btnRight = document.getElementById("moveRight");

let curIdx = 0; //page index as the user sees it, 0-2.
const maxPageIdx = 2;
const totalSlides = slides.length; //currently 9 imgs.

/* Make two 'ghost' start and end slides */
for (let i = 0; i < 3; i++) {

  /* Clone first 3 slides to end */
  let clone = slides[i].cloneNode(true);
  slider.appendChild(clone);

  /* Clone last 3 slides to start (-3+i to preserve slide order) */
  clone = slides[totalSlides-3+i].cloneNode(true);
  slider.insertBefore(clone, slides[0]);
}

/* Update slides info to include the clones. 
Note that at this point from user's POV there are 3 slides with idx 0-2, 
but there are actually 5 slides with idx 0-4.*/
const allSlides = document.querySelectorAll(".album"); //now there's 9 + 3x2 = 15 imgs
const newTotalSlides = allSlides.length;

/* Listen to clicks on left/right handle buttons */
document.addEventListener("click", e => {

  let handle

  if (e.target.matches(".handle")) {
    handle = e.target
  } else {
    handle = e.target.closest(".handle")
  }

  if (handle != null) {
    onHandleClick(handle)
  }
})

/* Actual function that handles left/right click */
function onHandleClick(handle) {

  /* Initial sliderIndex is set to 1 to show actual first slide. 
  Function in CSS: transform: translateX(calc(var(--slider-index) * -100%));
  -100x% = moves to page with index 'x'*/ 
  const sliderIndex = parseInt(getComputedStyle(slider).getPropertyValue("--slider-index"));

  /* If user clicks left handle */
  if (handle.classList.contains("left-handle")) {

    /* Decrement user's POV page idx */
    curIdx--;

    /* Edge case: Move from page 0 to page 2 with left click */
    if (curIdx < 0) {

      /* If idx overflows to -1, set it to 2 */
      curIdx = maxPageIdx;

      // console.log("Left, Inside IF: current page idx is " + curIdx);

      /* First, transition to cloned page 2.
      Do this by changing sliderindex to 0 */
      slider.style.transition = "transform 300ms ease-in-out";
      slider.style.setProperty("--slider-index", 0);
      // console.log("transitioned to cloned page 2");

      /* Then, wait 250ms (for transition to cloned page above) 
      before transforming to user's POV page 2 (with no visible transition).
      User's POV page 2 has actual index 3. */
      setTimeout(() => {
      slider.style.transition = "none";

      slider.style.setProperty("--slider-index", 3);
      // console.log("transitioned to real page 2");
      }, 250)

    /* Normal case: move to slide on the left */
    } else {
      // console.log("Left, Inside ELSE: current page idx is " + curIdx);
      slider.style.transition = "transform 300ms ease-in-out";
      /* curIdx+1 to match user's POV page indexes (0,1,2) with actual page indexes (1,2,3) */
      slider.style.setProperty("--slider-index", curIdx+1);
    }
  }

  /* If user clicks right handle */
  if (handle.classList.contains("right-handle")) {

    /* Increment user's POV page idx */
    curIdx++;

    /* Edge case: Move from page 2 to page 0 with right click */
    if (curIdx > maxPageIdx) {

      /* If idx overflows to 3, set it to 0 */
      curIdx = 0;

      // console.log("Right, Inside IF: current page idx is " + curIdx);

      /* First, transition to cloned page 0.
      Do this by changing sliderindex to 4*/
      slider.style.transition = "transform 300ms ease-in-out";
      slider.style.setProperty("--slider-index", 4);
      // console.log("transitioned to cloned page 0");

      /* Then, wait 250ms (for transition to cloned page above) 
      before transforming to user's POV page 0 (with no visible transition).
      User's POV page 0 has actual index 1. */
      setTimeout(() => {
      slider.style.transition = "none";
      slider.style.setProperty("--slider-index", 1);
      // console.log("transitioned to real page 0");
      }, 250)

    /* Normal case: move to slide on the left */  
    } else {
      // console.log("Right, Inside ELSE: current page idx is " + curIdx);
      slider.style.transition = "transform 300ms ease-in-out";
      /* curIdx+1 to match user's POV page indexes (0,1,2) with actual page indexes (1,2,3) */
      slider.style.setProperty("--slider-index", curIdx+1);
    }
  }
}


function changeBgColor() {

  const modal_viewer = document.getElementById("modal-viewer");
  const bg_color = getComputedStyle(modal_viewer).getPropertyValue("--bg-color");

  /* White -> Gray */
  if (bg_color == 0) {
    modal_viewer.classList.replace("modal-viewer-white", "modal-viewer-gray");
    modal_viewer.style.setProperty("--bg-color", 1);

  /* Gray -> Black */
  } else if (bg_color == 1) {
    modal_viewer.classList.replace("modal-viewer-gray", "modal-viewer-black");
    modal_viewer.style.setProperty("--bg-color", 2);

  /* Black -> White */
  } else if (bg_color == 2) {
    modal_viewer.classList.replace("modal-viewer-black", "modal-viewer-white");
    modal_viewer.style.setProperty("--bg-color", 0);
  }
}

function close_modal() {

  const modal_viewer = document.getElementById("modal-viewer");
  const gallery = document.getElementById("gallery-box")
  modal_viewer.style.display = "None";
  gallery.style.display = "None";

}

function display_modal() {

  const gallery = document.getElementById("gallery-box");
  const modal_viewer = document.getElementById("modal-viewer");

  modal_viewer.style.display = "Block";
  gallery.style.display = "Block";

}



function initializeSlider() {
  if (slides_gallery.length > 0) {
    slides_gallery[slideIdx].classList.add("displaySlide");
    // intervalId = setInterval(nextSlide, 5000);
  }
}

function showSlide() {

  // console.log(slideIdx);

  if (slideIdx >= slides_gallery.length) {
    slideIdx = 0;
  } else if (slideIdx < 0) {
    slideIdx = slides_gallery.length - 1;
  }

  slides_gallery.forEach(slide => {
    slide.classList.remove("displaySlide");
  });

  slides_gallery[slideIdx].classList.add("displaySlide");
  
  const count = `${slideIdx+1} / ${slides_gallery.length}`;
  console.log(count);

  document.getElementById("modal-counter").innerHTML = count;
}

function prevSlide() {
  slideIdx--;
  showSlide();
}

function nextSlide() {
  slideIdx++;
  showSlide();
}

function toggleGrid() {

  const gallery_single = document.getElementById("gallery-single");
  const gallery_grid = document.getElementById("gallery-grid");
  const display_next = document.getElementById("modal-grid-button").innerHTML;
  const counter = document.getElementById("modal-counter");
  
  // If currently on grid display
  if (display_next=="Slide") {
    // Change button text to "Grid"
    document.getElementById("modal-grid-button").innerHTML = "Grid";

    // Hide grid display, reveal slide
    gallery_single.style.display = "Block";
    gallery_grid.style.display = "None";
    
    // Show slide counter
    counter.style.display = "Block";
  
  // If currently on slide display
  } else {
    // console.log("Slide -> Grid");
    // Hide slide display, reveal grid
    gallery_single.style.display = "None";
    gallery_grid.style.display = "Block";
    
    // Remove slide counter
    counter.style.display = "None";

    // Change button text to "Grid"
    document.getElementById("modal-grid-button").innerHTML = "Slide";

  }

}


