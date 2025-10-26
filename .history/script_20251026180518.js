// MORINA BAUSTOFFE - MODERN 3D DESIGN/* ==================================// Initialize AOS

// Initialize everything when DOM loads

document.addEventListener('DOMContentLoaded', function() {   MORINA BAUSTOFFE - MODERN JAVASCRIPTAOS.init({

  

  // Register GSAP ScrollTrigger   GSAP Animations + 3D Effects  duration: 1000,

  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {

    gsap.registerPlugin(ScrollTrigger);   ================================== */  once: true,

  }

  });

  // === NAVBAR SCROLL EFFECT ===

  const navbar = document.querySelector('.navbar');// Wait for DOM to load

  window.addEventListener('scroll', () => {

    if (window.scrollY > 50) {document.addEventListener('DOMContentLoaded', function() {// Mobile menu toggle

      navbar.classList.add('scrolled');

    } else {  const mobileMenu = document.querySelector('.mobile-menu');

      navbar.classList.remove('scrolled');

    }  // Initialize GSAP ScrollTriggerconst navLinks = document.querySelector('.nav-links');

  });

    gsap.registerPlugin(ScrollTrigger);

  // === MOBILE MENU TOGGLE ===

  const mobileMenu = document.querySelector('.mobile-menu');  if (mobileMenu && navLinks) {

  const navLinks = document.querySelector('.nav-links');

    // ===== NAVBAR SCROLL EFFECT =====  mobileMenu.addEventListener('click', () => {

  if (mobileMenu && navLinks) {

    mobileMenu.addEventListener('click', () => {  const navbar = document.querySelector('.navbar');    navLinks.classList.toggle('active');

      navLinks.classList.toggle('active');

      mobileMenu.classList.toggle('active');  window.addEventListener('scroll', () => {    mobileMenu.classList.toggle('active');

    });

        if (window.scrollY > 50) {  });

    navLinks.querySelectorAll('a').forEach(link => {

      link.addEventListener('click', () => {      navbar.classList.add('scrolled');  

        navLinks.classList.remove('active');

        mobileMenu.classList.remove('active');    } else {  // Close menu when clicking on a link

      });

    });      navbar.classList.remove('scrolled');  navLinks.querySelectorAll('a').forEach(link => {

    

    document.addEventListener('click', (e) => {    }    link.addEventListener('click', () => {

      if (!mobileMenu.contains(e.target) && !navLinks.contains(e.target)) {

        navLinks.classList.remove('active');  });      navLinks.classList.remove('active');

        mobileMenu.classList.remove('active');

      }        mobileMenu.classList.remove('active');

    });

  }  // ===== MOBILE MENU TOGGLE =====    });

  

  // === SMOOTH SCROLLING ===  const mobileMenu = document.querySelector('.mobile-menu');  });

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {

    anchor.addEventListener('click', function (e) {  const navLinks = document.querySelector('.nav-links');  

      e.preventDefault();

      const target = document.querySelector(this.getAttribute('href'));    // Close menu when clicking outside

      if (target) {

        target.scrollIntoView({behavior: 'smooth'});  if (mobileMenu && navLinks) {  document.addEventListener('click', (e) => {

      }

    });    mobileMenu.addEventListener('click', () => {    if (!mobileMenu.contains(e.target) && !navLinks.contains(e.target)) {

  });

        navLinks.classList.toggle('active');      navLinks.classList.remove('active');

  // === GSAP HERO ANIMATIONS ===

  if (typeof gsap !== 'undefined') {      mobileMenu.classList.toggle('active');      mobileMenu.classList.remove('active');

    gsap.from('.badge', {opacity: 0, y: 30, duration: 0.8, delay: 0.2});

    gsap.from('.hero-title .title-line', {opacity: 0, y: 50, duration: 0.8, stagger: 0.2, delay: 0.4});    });    }

    gsap.from('.hero-subtitle', {opacity: 0, y: 30, duration: 0.8, delay: 1});

    gsap.from('.hero-cta .btn', {opacity: 0, y: 30, duration: 0.6, stagger: 0.15, delay: 1.2});      });

    gsap.from('.hero-stats', {opacity: 0, y: 30, duration: 0.8, delay: 1.4});

    gsap.from('.visual-card', {opacity: 0, scale: 0.8, duration: 1, delay: 0.6});    // Close menu when clicking on a link}

    gsap.from('.float-icon', {opacity: 0, scale: 0, duration: 0.6, stagger: 0.15, delay: 1.5});

        navLinks.querySelectorAll('a').forEach(link => {

    // Scroll-triggered animations

    const scrollElements = document.querySelectorAll('[data-scroll]');      link.addEventListener('click', () => {// Smooth scrolling for navigation links

    scrollElements.forEach((el) => {

      gsap.from(el, {        navLinks.classList.remove('active');document.querySelectorAll('a[href^="#"]').forEach(anchor => {

        scrollTrigger: {trigger: el, start: 'top 85%', toggleActions: 'play none none none'},

        opacity: 0, y: 50, duration: 0.8        mobileMenu.classList.remove('active');  anchor.addEventListener('click', function (e) {

      });

    });      });    e.preventDefault();

    

    // Parallax effect    });    const target = document.querySelector(this.getAttribute('href'));

    gsap.to('.hero-content', {y: -50, scrollTrigger: {trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true}});

    gsap.to('.hero-visual', {y: -80, scrollTrigger: {trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true}});        if (target) {

    

    // Floating icons animation    // Close menu when clicking outside      target.scrollIntoView({

    gsap.to('.float-icon', {

      y: '+=15', rotation: '+=5', duration: 2, ease: 'sine.inOut',    document.addEventListener('click', (e) => {        behavior: 'smooth'

      stagger: {each: 0.2, repeat: -1, yoyo: true}

    });      if (!mobileMenu.contains(e.target) && !navLinks.contains(e.target)) {      });

    

    // Gradient spheres animation        navLinks.classList.remove('active');    }

    gsap.to('.gradient-sphere.sphere-1', {x: '+=50', y: '+=30', scale: 1.1, duration: 15, repeat: -1, yoyo: true, ease: 'sine.inOut'});

    gsap.to('.gradient-sphere.sphere-2', {x: '-=30', y: '-=50', scale: 0.9, duration: 18, repeat: -1, yoyo: true, ease: 'sine.inOut'});        mobileMenu.classList.remove('active');  });

    gsap.to('.gradient-sphere.sphere-3', {x: '+=40', y: '-=20', scale: 1.05, duration: 12, repeat: -1, yoyo: true, ease: 'sine.inOut'});

          }});

    // Service cards stagger

    gsap.from('.service-card', {    });

      scrollTrigger: {trigger: '.services-grid', start: 'top 80%', toggleActions: 'play none none none'},

      opacity: 0, y: 50, duration: 0.6, stagger: 0.15  }// Gallery navigation variables

    });

      let currentImageIndex = 0;

    // Feature cards stagger

    gsap.from('.feature-card', {  // ===== SMOOTH SCROLLING =====let galleryImages = [];

      scrollTrigger: {trigger: '.features-grid', start: 'top 80%', toggleActions: 'play none none none'},

      opacity: 0, y: 50, duration: 0.6, stagger: 0.15  document.querySelectorAll('a[href^="#"]').forEach(anchor => {

    });

        anchor.addEventListener('click', function (e) {// Initialize gallery images array

    // Product cards stagger

    gsap.from('.product-category-card', {      e.preventDefault();function initializeGallery() {

      scrollTrigger: {trigger: '.products-grid', start: 'top 80%', toggleActions: 'play none none none'},

      opacity: 0, scale: 0.9, duration: 0.6, stagger: 0.15      const target = document.querySelector(this.getAttribute('href'));  galleryImages = [];

    });

  }      if (target) {  const galleryItems = document.querySelectorAll('.gallery-item');

  

  // === 3D TILT EFFECT FOR HERO CARD ===        target.scrollIntoView({  

  const visualCard = document.querySelector('.visual-card');

  if (visualCard && typeof gsap !== 'undefined') {          behavior: 'smooth'  galleryItems.forEach((item, index) => {

    visualCard.addEventListener('mousemove', (e) => {

      const rect = visualCard.getBoundingClientRect();        });    const img = item.querySelector('img');

      const x = e.clientX - rect.left;

      const y = e.clientY - rect.top;      }    const titleElement = item.querySelector('h3');

      const centerX = rect.width / 2;

      const centerY = rect.height / 2;    });    

      const rotateX = ((y - centerY) / centerY) * -10;

      const rotateY = ((x - centerX) / centerX) * 10;  });    if (img && titleElement) {

      gsap.to(visualCard, {duration: 0.3, rotateX: rotateX, rotateY: rotateY, transformPerspective: 1000, ease: 'power1.out'});

    });        galleryImages.push({

    

    visualCard.addEventListener('mouseleave', () => {  // ===== GSAP HERO ANIMATIONS =====        src: img.src,

      gsap.to(visualCard, {duration: 0.5, rotateX: 0, rotateY: 0, ease: 'power2.out'});

    });  gsap.from('.badge', {        title: titleElement.textContent,

  }

      opacity: 0,        index: index

  // === PRODUCT CATEGORY TOGGLE ===

  const categoryHeaders = document.querySelectorAll('.category-header');    y: 30,      });

  categoryHeaders.forEach(header => {

    header.addEventListener('click', function() {    duration: 0.8,    }

      const card = this.closest('.product-category-card');

      const wrapper = card.querySelector('.catalogs-wrapper');    delay: 0.2  });

      const isOpen = card.classList.contains('open');

        });}

      // Close all other cards

      document.querySelectorAll('.product-category-card').forEach(otherCard => {  

        if (otherCard !== card) {

          otherCard.classList.remove('open');  gsap.from('.hero-title .title-line', {// Close all product accordions (utility)

          const otherWrapper = otherCard.querySelector('.catalogs-wrapper');

          if (typeof gsap !== 'undefined') {    opacity: 0,function closeAllAccordions() {

            gsap.to(otherWrapper, {maxHeight: 0, duration: 0.3, ease: 'power2.out'});

          }    y: 50,  document.querySelectorAll('.product-acc').forEach(acc => acc.classList.remove('open'));

        }

      });    duration: 0.8,  document.querySelectorAll('.catalog-list').forEach(list => list.hidden = true);

      

      // Toggle current card    stagger: 0.2,  document.querySelectorAll('.acc-btn').forEach(btn => btn.setAttribute('aria-expanded', 'false'));

      if (isOpen) {

        card.classList.remove('open');    delay: 0.4}

        if (typeof gsap !== 'undefined') {

          gsap.to(wrapper, {maxHeight: 0, duration: 0.3, ease: 'power2.out'});  });

        }

      } else {  // Full Screen Modal Functions

        card.classList.add('open');

        if (typeof gsap !== 'undefined') {  gsap.from('.hero-subtitle', {function openFullScreen(imageSrc, imageTitle) {

          gsap.to(wrapper, {maxHeight: 800, duration: 0.4, ease: 'power2.out'});

        }    opacity: 0,  const modal = document.getElementById('fullscreen-modal');

      }

    });    y: 30,  const modalImage = document.getElementById('modal-image');

  });

      duration: 0.8,  const modalCaption = document.getElementById('modal-caption');

  // === SCROLL TO TOP BUTTON ===

  const scrollBtn = document.createElement('button');    delay: 1  

  scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';

  scrollBtn.style.cssText = 'position:fixed;bottom:30px;right:30px;width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff6b35,#f77f00);border:none;color:#fff;font-size:1.2rem;cursor:pointer;opacity:0;visibility:hidden;transition:all .3s;z-index:999;box-shadow:0 8px 24px rgba(255,107,53,0.3)';  });  if (!modal || !modalImage || !modalCaption) return;

  document.body.appendChild(scrollBtn);

      

  window.addEventListener('scroll', () => {

    if (window.scrollY > 300) {  gsap.from('.hero-cta .btn', {  // Initialize gallery if not done yet

      scrollBtn.style.opacity = '1';

      scrollBtn.style.visibility = 'visible';    opacity: 0,  if (galleryImages.length === 0) {

    } else {

      scrollBtn.style.opacity = '0';    y: 30,    initializeGallery();

      scrollBtn.style.visibility = 'hidden';

    }    duration: 0.6,  }

  });

      stagger: 0.15,  

  scrollBtn.addEventListener('click', () => {

    window.scrollTo({top: 0, behavior: 'smooth'});    delay: 1.2  // Find current image index

  });

    });  currentImageIndex = galleryImages.findIndex(img => img.src === imageSrc);

  scrollBtn.addEventListener('mouseenter', () => scrollBtn.style.transform = 'scale(1.1)');

  scrollBtn.addEventListener('mouseleave', () => scrollBtn.style.transform = 'scale(1)');    if (currentImageIndex === -1) currentImageIndex = 0;

  

  console.log('🚀 Morina Baustoffe - Modern 3D Design Loaded');  gsap.from('.hero-stats', {  

});

    opacity: 0,  modal.classList.add('active');

    y: 30,  modalImage.src = imageSrc;

    duration: 0.8,  modalImage.alt = imageTitle;

    delay: 1.4  modalCaption.textContent = imageTitle;

  });  

    // Show navigation arrows

  gsap.from('.visual-card', {  const leftArrow = modal.querySelector('.nav-arrow-left');

    opacity: 0,  const rightArrow = modal.querySelector('.nav-arrow-right');

    scale: 0.8,  

    duration: 1,  if (leftArrow) leftArrow.style.display = 'flex';

    delay: 0.6  if (rightArrow) rightArrow.style.display = 'flex';

  });  

    // Update navigation arrows

  gsap.from('.float-icon', {  updateNavigationArrows();

    opacity: 0,  

    scale: 0,  // Prevent body scroll when modal is open

    duration: 0.6,  document.body.style.overflow = 'hidden';

    stagger: 0.15,  

    delay: 1.5  // Add keyboard event listeners

  });  document.addEventListener('keydown', handleKeyNavigation);

  }

  // ===== SCROLL TRIGGERED ANIMATIONS =====

  const scrollElements = document.querySelectorAll('[data-scroll]');function closeFullScreen() {

    const modal = document.getElementById('fullscreen-modal');

  scrollElements.forEach((el) => {  if (!modal) return;

    gsap.from(el, {  

      scrollTrigger: {  modal.classList.remove('active');

        trigger: el,  

        start: 'top 85%',  // Restore body scroll

        toggleActions: 'play none none none'  document.body.style.overflow = 'auto';

      },  

      opacity: 0,  // Remove keyboard event listeners

      y: 50,  document.removeEventListener('keydown', handleKeyNavigation);

      duration: 0.8}

    });

  });function nextImage() {

    if (currentImageIndex < galleryImages.length - 1) {

  // ===== 3D CARD TILT EFFECT (Hero Visual) =====    currentImageIndex++;

  const visualCard = document.querySelector('.visual-card');    updateModalImage();

  if (visualCard) {  }

    visualCard.addEventListener('mousemove', (e) => {}

      const rect = visualCard.getBoundingClientRect();

      const x = e.clientX - rect.left;function previousImage() {

      const y = e.clientY - rect.top;  if (currentImageIndex > 0) {

          currentImageIndex--;

      const centerX = rect.width / 2;    updateModalImage();

      const centerY = rect.height / 2;  }

      }

      const rotateX = ((y - centerY) / centerY) * -10; // Max 10 degrees

      const rotateY = ((x - centerX) / centerX) * 10;function updateModalImage() {

        const modalImage = document.getElementById('modal-image');

      gsap.to(visualCard, {  const modalCaption = document.getElementById('modal-caption');

        duration: 0.3,  

        rotateX: rotateX,  if (!modalImage || !modalCaption || !galleryImages[currentImageIndex]) return;

        rotateY: rotateY,  

        transformPerspective: 1000,  const currentImage = galleryImages[currentImageIndex];

        ease: 'power1.out'  

      });  modalImage.src = currentImage.src;

    });  modalImage.alt = currentImage.title;

      modalCaption.textContent = currentImage.title;

    visualCard.addEventListener('mouseleave', () => {  

      gsap.to(visualCard, {  updateNavigationArrows();

        duration: 0.5,}

        rotateX: 0,

        rotateY: 0,function updateNavigationArrows() {

        ease: 'power2.out'  const modal = document.getElementById('fullscreen-modal');

      });  if (!modal) return;

    });  

  }  const leftArrow = modal.querySelector('.nav-arrow-left');

    const rightArrow = modal.querySelector('.nav-arrow-right');

  // ===== PARALLAX EFFECT FOR HERO =====  

  gsap.to('.hero-content', {  if (!leftArrow || !rightArrow) return;

    y: -50,  

    scrollTrigger: {  // Disable/enable left arrow

      trigger: '.hero',  if (currentImageIndex === 0) {

      start: 'top top',    leftArrow.classList.add('disabled');

      end: 'bottom top',  } else {

      scrub: true    leftArrow.classList.remove('disabled');

    }  }

  });  

    // Disable/enable right arrow

  gsap.to('.hero-visual', {  if (currentImageIndex === galleryImages.length - 1) {

    y: -80,    rightArrow.classList.add('disabled');

    scrollTrigger: {  } else {

      trigger: '.hero',    rightArrow.classList.remove('disabled');

      start: 'top top',  }

      end: 'bottom top',}

      scrub: true

    }function handleKeyNavigation(event) {

  });  switch(event.key) {

      case 'Escape':

  // ===== PRODUCT CATEGORY TOGGLE =====      closeFullScreen();

  const categoryHeaders = document.querySelectorAll('.category-header');      break;

      case 'ArrowLeft':

  categoryHeaders.forEach(header => {      event.preventDefault();

    header.addEventListener('click', function() {      previousImage();

      const card = this.closest('.product-category-card');      break;

      const wrapper = card.querySelector('.catalogs-wrapper');    case 'ArrowRight':

      const isOpen = card.classList.contains('open');      event.preventDefault();

            nextImage();

      // Close all other cards      break;

      document.querySelectorAll('.product-category-card').forEach(otherCard => {  }

        if (otherCard !== card) {}

          otherCard.classList.remove('open');

          const otherWrapper = otherCard.querySelector('.catalogs-wrapper');// Initialize everything when DOM is loaded

          gsap.to(otherWrapper, {document.addEventListener('DOMContentLoaded', function() {

            maxHeight: 0,  // Initialize gallery

            duration: 0.3,  initializeGallery();

            ease: 'power2.out'  // Ensure Products dropdowns start closed on fresh load

          });  closeAllAccordions();

        }  

      });  // Close modal when clicking outside the image

        const modal = document.getElementById('fullscreen-modal');

      // Toggle current card  if (modal) {

      if (isOpen) {    modal.addEventListener('click', function(event) {

        card.classList.remove('open');      if (event.target === this) {

        gsap.to(wrapper, {        closeFullScreen();

          maxHeight: 0,      }

          duration: 0.3,    });

          ease: 'power2.out'  }

        });  

      } else {  // Prevent modal from closing when clicking on the modal content

        card.classList.add('open');  const modalContent = modal?.querySelector('.modal-content');

        gsap.to(wrapper, {  if (modalContent) {

          maxHeight: 800,    modalContent.addEventListener('click', function(event) {

          duration: 0.4,      event.stopPropagation();

          ease: 'power2.out'    });

        });  }

      }  

    });  // Close button functionality

  });  const closeBtn = modal?.querySelector('.close-btn');

    if (closeBtn) {

  // ===== CURSOR FOLLOWER (Desktop only) =====    closeBtn.addEventListener('click', closeFullScreen);

  if (window.innerWidth > 768) {  }

    const cursor = document.createElement('div');  

    cursor.className = 'custom-cursor';  // Navigation arrows functionality

    cursor.style.cssText = `  const leftArrow = modal?.querySelector('.nav-arrow-left');

      position: fixed;  const rightArrow = modal?.querySelector('.nav-arrow-right');

      width: 20px;  

      height: 20px;  if (leftArrow) {

      border-radius: 50%;    leftArrow.addEventListener('click', function(event) {

      background: rgba(255, 107, 53, 0.5);      event.stopPropagation();

      pointer-events: none;      previousImage();

      z-index: 9999;    });

      mix-blend-mode: difference;  }

      transition: transform 0.15s ease;  

    `;  if (rightArrow) {

    document.body.appendChild(cursor);    rightArrow.addEventListener('click', function(event) {

          event.stopPropagation();

    let mouseX = 0;      nextImage();

    let mouseY = 0;    });

    let cursorX = 0;  }

    let cursorY = 0;});

    

    document.addEventListener('mousemove', (e) => {// Handle bfcache (back/forward navigation) to ensure dropdowns are closed

      mouseX = e.clientX;window.addEventListener('pageshow', function(e) {

      mouseY = e.clientY;  closeAllAccordions();

    });});

    

    function animate() {// Navbar scroll effect

      const dx = mouseX - cursorX;window.addEventListener('scroll', () => {

      const dy = mouseY - cursorY;  const navbar = document.querySelector('.navbar');

        if (navbar) {

      cursorX += dx * 0.1;    if (window.scrollY > 100) {

      cursorY += dy * 0.1;      navbar.classList.add('scrolled');

          } else {

      cursor.style.left = cursorX - 10 + 'px';      navbar.classList.remove('scrolled');

      cursor.style.top = cursorY - 10 + 'px';    }

        }

      requestAnimationFrame(animate);});

    }

    animate();// Hero parallax effect

    window.addEventListener('scroll', () => {

    // Enlarge cursor on hover over interactive elements  const scrolled = window.pageYOffset;

    const interactiveElements = document.querySelectorAll('a, button, .btn, .catalog-tag');  const parallax = document.querySelector('.hero-bg');

    interactiveElements.forEach(el => {  if (parallax) {

      el.addEventListener('mouseenter', () => {    const speed = scrolled * 0.5;

        cursor.style.transform = 'scale(2)';    parallax.style.transform = `translateY(${speed}px)`;

      });  }

      el.addEventListener('mouseleave', () => {});

        cursor.style.transform = 'scale(1)';

      });// Products accordion: vertical open/close (open only via button)

    });document.addEventListener('click', (e) => {

  }  const accBtn = e.target.closest('.acc-btn');

    const acc = e.target.closest('.product-acc');

  // ===== STATS COUNTER ANIMATION =====

  const statNumbers = document.querySelectorAll('.stat-number');  // Close if clicking outside any accordion

    if (!accBtn) {

  statNumbers.forEach(stat => {    // Click anywhere outside: close all open dropdowns

    const text = stat.textContent;    if (!e.target.closest('.catalog-list')) {

    const hasPlus = text.includes('+');      closeAllAccordions();

    const hasPercent = text.includes('%');    }

    const number = parseInt(text.replace(/\D/g, ''));    return;

      }

    if (!isNaN(number)) {

      gsap.from(stat, {  // Toggle clicked accordion

        scrollTrigger: {  if (acc && accBtn) {

          trigger: stat,    e.preventDefault();

          start: 'top 80%',    const isOpen = acc.classList.toggle('open');

          toggleActions: 'play none none none'    const list = acc.querySelector('.catalog-list');

        },    accBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        textContent: 0,    if (list) list.hidden = !isOpen;

        duration: 2,

        ease: 'power1.out',    // Optionally close others

        snap: { textContent: 1 },    document.querySelectorAll('.product-acc').forEach(other => {

        onUpdate: function() {      if (other !== acc) {

          const current = Math.floor(this.targets()[0].textContent);        other.classList.remove('open');

          stat.textContent = current + (hasPlus ? '+' : '') + (hasPercent ? '%' : '');        const l = other.querySelector('.catalog-list');

        }        const b = other.querySelector('.acc-btn');

      });        if (l) l.hidden = true;

    }        if (b) b.setAttribute('aria-expanded', 'false');

  });      }

      });

  // ===== FLOATING ANIMATION FOR HERO ICONS =====  }

  gsap.to('.float-icon', {});

    y: '+=15',
    rotation: '+=5',
    duration: 2,
    ease: 'sine.inOut',
    stagger: {
      each: 0.2,
      repeat: -1,
      yoyo: true
    }
  });
  
  // ===== GRADIENT SPHERE ANIMATIONS =====
  gsap.to('.gradient-sphere.sphere-1', {
    x: '+=50',
    y: '+=30',
    scale: 1.1,
    duration: 15,
    repeat: -1,
    yoyo: true,
    ease: 'sine.inOut'
  });
  
  gsap.to('.gradient-sphere.sphere-2', {
    x: '-=30',
    y: '-=50',
    scale: 0.9,
    duration: 18,
    repeat: -1,
    yoyo: true,
    ease: 'sine.inOut'
  });
  
  gsap.to('.gradient-sphere.sphere-3', {
    x: '+=40',
    y: '-=20',
    scale: 1.05,
    duration: 12,
    repeat: -1,
    yoyo: true,
    ease: 'sine.inOut'
  });
  
  // ===== SERVICE CARDS STAGGER ANIMATION =====
  gsap.from('.service-card', {
    scrollTrigger: {
      trigger: '.services-grid',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    opacity: 0,
    y: 50,
    duration: 0.6,
    stagger: 0.15
  });
  
  // ===== FEATURE CARDS STAGGER ANIMATION =====
  gsap.from('.feature-card', {
    scrollTrigger: {
      trigger: '.features-grid',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    opacity: 0,
    y: 50,
    duration: 0.6,
    stagger: 0.15
  });
  
  // ===== PRODUCT CARDS STAGGER ANIMATION =====
  gsap.from('.product-category-card', {
    scrollTrigger: {
      trigger: '.products-grid',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    opacity: 0,
    scale: 0.9,
    duration: 0.6,
    stagger: 0.15
  });
  
  // ===== SCROLL TO TOP BUTTON =====
  const scrollToTopBtn = document.createElement('button');
  scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
  scrollToTopBtn.className = 'scroll-to-top';
  scrollToTopBtn.style.cssText = `
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6b35 0%, #f77f00 100%);
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
  `;
  document.body.appendChild(scrollToTopBtn);
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      scrollToTopBtn.style.opacity = '1';
      scrollToTopBtn.style.visibility = 'visible';
    } else {
      scrollToTopBtn.style.opacity = '0';
      scrollToTopBtn.style.visibility = 'hidden';
    }
  });
  
  scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
  
  scrollToTopBtn.addEventListener('mouseenter', () => {
    scrollToTopBtn.style.transform = 'scale(1.1)';
  });
  
  scrollToTopBtn.addEventListener('mouseleave', () => {
    scrollToTopBtn.style.transform = 'scale(1)';
  });
  
  console.log('🚀 Morina Baustoffe - Modern Design Loaded');
});

// ===== LOADING ANIMATION =====
window.addEventListener('load', () => {
  gsap.to('body', {
    opacity: 1,
    duration: 0.5
  });
});
