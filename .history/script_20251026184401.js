// MORINA BAUSTOFFE - MODERN 3D DESIGN  
document.addEventListener('DOMContentLoaded',function(){if(typeof gsap!=='undefined'&&typeof ScrollTrigger!=='undefined'){gsap.registerPlugin(ScrollTrigger);}
const navbar=document.querySelector('.navbar');window.addEventListener('scroll',()=>{if(window.scrollY>50){navbar.classList.add('scrolled');}else{navbar.classList.remove('scrolled');}});
const mobileMenu=document.querySelector('.mobile-menu');const navLinks=document.querySelector('.nav-links');if(mobileMenu&&navLinks){mobileMenu.addEventListener('click',()=>{navLinks.classList.toggle('active');mobileMenu.classList.toggle('active');});navLinks.querySelectorAll('a').forEach(link=>{link.addEventListener('click',()=>{navLinks.classList.remove('active');mobileMenu.classList.remove('active');});});document.addEventListener('click',(e)=>{if(!mobileMenu.contains(e.target)&&!navLinks.contains(e.target)){navLinks.classList.remove('active');mobileMenu.classList.remove('active');}});}
document.querySelectorAll('a[href^="#"]').forEach(anchor=>{anchor.addEventListener('click',function(e){e.preventDefault();const target=document.querySelector(this.getAttribute('href'));if(target){target.scrollIntoView({behavior:'smooth'});}});});
if(typeof gsap!=='undefined'){gsap.from('.badge',{opacity:0,y:30,duration:0.8,delay:0.2});gsap.from('.hero-title .title-line',{opacity:0,y:50,duration:0.8,stagger:0.2,delay:0.4});gsap.from('.hero-subtitle',{opacity:0,y:30,duration:0.8,delay:1});gsap.from('.hero-cta .btn',{opacity:0,y:30,duration:0.6,stagger:0.15,delay:1.2});gsap.from('.hero-stats',{opacity:0,y:30,duration:0.8,delay:1.4});gsap.from('.visual-card',{opacity:0,scale:0.8,duration:1,delay:0.6});gsap.from('.float-icon',{opacity:0,scale:0,duration:0.6,stagger:0.15,delay:1.5});
const scrollElements=document.querySelectorAll('[data-scroll]');scrollElements.forEach((el)=>{gsap.from(el,{scrollTrigger:{trigger:el,start:'top 85%',toggleActions:'play none none none'},opacity:0,y:50,duration:0.8});});
gsap.to('.hero-content',{y:-50,scrollTrigger:{trigger:'.hero',start:'top top',end:'bottom top',scrub:true}});gsap.to('.hero-visual',{y:-80,scrollTrigger:{trigger:'.hero',start:'top top',end:'bottom top',scrub:true}});
gsap.to('.float-icon',{y:'+=15',rotation:'+=5',duration:2,ease:'sine.inOut',stagger:{each:0.2,repeat:-1,yoyo:true}});
gsap.to('.gradient-sphere.sphere-1',{x:'+=50',y:'+=30',scale:1.1,duration:15,repeat:-1,yoyo:true,ease:'sine.inOut'});gsap.to('.gradient-sphere.sphere-2',{x:'-=30',y:'-=50',scale:0.9,duration:18,repeat:-1,yoyo:true,ease:'sine.inOut'});gsap.to('.gradient-sphere.sphere-3',{x:'+=40',y:'-=20',scale:1.05,duration:12,repeat:-1,yoyo:true,ease:'sine.inOut'});
gsap.from('.service-card',{scrollTrigger:{trigger:'.services-grid',start:'top 80%',toggleActions:'play none none none'},opacity:0,y:50,duration:0.6,stagger:0.15});gsap.from('.feature-card',{scrollTrigger:{trigger:'.features-grid',start:'top 80%',toggleActions:'play none none none'},opacity:0,y:50,duration:0.6,stagger:0.15});gsap.from('.product-category-card',{scrollTrigger:{trigger:'.products-grid',start:'top 80%',toggleActions:'play none none none'},opacity:0,scale:0.9,duration:0.6,stagger:0.15});}
const visualCard=document.querySelector('.visual-card');if(visualCard&&typeof gsap!=='undefined'){visualCard.addEventListener('mousemove',(e)=>{const rect=visualCard.getBoundingClientRect();const x=e.clientX-rect.left;const y=e.clientY-rect.top;const centerX=rect.width/2;const centerY=rect.height/2;const rotateX=((y-centerY)/centerY)*-10;const rotateY=((x-centerX)/centerX)*10;gsap.to(visualCard,{duration:0.3,rotateX:rotateX,rotateY:rotateY,transformPerspective:1000,ease:'power1.out'});});visualCard.addEventListener('mouseleave',()=>{gsap.to(visualCard,{duration:0.5,rotateX:0,rotateY:0,ease:'power2.out'});});}
const categoryHeaders=document.querySelectorAll('.category-header');categoryHeaders.forEach(header=>{header.addEventListener('click',function(){const card=this.closest('.product-category-card');const wrapper=card.querySelector('.catalogs-wrapper');const isOpen=card.classList.contains('open');document.querySelectorAll('.product-category-card').forEach(otherCard=>{if(otherCard!==card){otherCard.classList.remove('open');const otherWrapper=otherCard.querySelector('.catalogs-wrapper');if(typeof gsap!=='undefined'){gsap.to(otherWrapper,{maxHeight:0,duration:0.3,ease:'power2.out'});}}});if(isOpen){card.classList.remove('open');if(typeof gsap!=='undefined'){gsap.to(wrapper,{maxHeight:0,duration:0.3,ease:'power2.out'});}}else{card.classList.add('open');if(typeof gsap!=='undefined'){gsap.to(wrapper,{maxHeight:800,duration:0.4,ease:'power2.out'});}}});});
const scrollBtn=document.createElement('button');scrollBtn.innerHTML='<i class="fas fa-arrow-up"></i>';scrollBtn.style.cssText='position:fixed;bottom:30px;right:30px;width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff6b35,#f77f00);border:none;color:#fff;font-size:1.2rem;cursor:pointer;opacity:0;visibility:hidden;transition:all .3s;z-index:999;box-shadow:0 8px 24px rgba(255,107,53,0.3)';document.body.appendChild(scrollBtn);
window.addEventListener('scroll',()=>{if(window.scrollY>300){scrollBtn.style.opacity='1';scrollBtn.style.visibility='visible';}else{scrollBtn.style.opacity='0';scrollBtn.style.visibility='hidden';}});
scrollBtn.addEventListener('click',()=>{window.scrollTo({top:0,behavior:'smooth'});});
scrollBtn.addEventListener('mouseenter',()=>scrollBtn.style.transform='scale(1.1)');scrollBtn.addEventListener('mouseleave',()=>scrollBtn.style.transform='scale(1)');
console.log('🚀 Morina Baustoffe - Modern 3D Design Loaded');});

// ====================================
// FULLSCREEN MODAL FUNCTIONS (Global)
// ====================================
let currentImageIndex = 0;
let imageArray = [];

function openFullScreen(imgSrc, caption) {
  const modal = document.getElementById('fullscreen-modal');
  const modalImage = document.getElementById('modal-image');
  const modalCaption = document.getElementById('modal-caption');
  
  if (modal && modalImage) {
    modalImage.src = imgSrc;
    modalCaption.textContent = caption || '';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

function closeFullScreen() {
  const modal = document.getElementById('fullscreen-modal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
}

function previousImage() {
  if (imageArray.length > 0) {
    currentImageIndex = (currentImageIndex - 1 + imageArray.length) % imageArray.length;
    const modalImage = document.getElementById('modal-image');
    const modalCaption = document.getElementById('modal-caption');
    if (modalImage && imageArray[currentImageIndex]) {
      modalImage.src = imageArray[currentImageIndex].src;
      modalCaption.textContent = imageArray[currentImageIndex].caption || '';
    }
  }
}

function nextImage() {
  if (imageArray.length > 0) {
    currentImageIndex = (currentImageIndex + 1) % imageArray.length;
    const modalImage = document.getElementById('modal-image');
    const modalCaption = document.getElementById('modal-caption');
    if (modalImage && imageArray[currentImageIndex]) {
      modalImage.src = imageArray[currentImageIndex].src;
      modalCaption.textContent = imageArray[currentImageIndex].caption || '';
    }
  }
}

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeFullScreen();
  }
});

// Close modal when clicking outside image
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('fullscreen-modal');
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeFullScreen();
      }
    });
  }
});