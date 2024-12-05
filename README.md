# Original project:
Repo: https://github.com/remuollinen/nenillo-photography/tree/main?tab=readme-ov-file  

Page: https://nenillophotography.netlify.app/  

Features:
- Lightbox: when click on image, full version pops up in a modal window.
- Back-to-top button for desktop.

# Features I added:
- Project carousel: User can browse projects in an infinite-looping carousel.
- Slideshow: When click on project thumbnail, a modal window pops up, user can brows photos in that project like a slideshow.
- Gridview: When click on 'Grid', user will see all photos in the project displayed in an Instagram-like grid.
- Additional image-viewing UI/UX:
    + Exit button for modal window
    + Background toggle between 3 colors: white, gray, black
    + Image counter for slideshow view
- Image download protection: Disabled right-click on <img> elements to discourage image theft.
- Lazy loading: Use IntersectionObserver API to load <img> elements only when they're about to enter viewport.
