(function() {
    'use strict';
    
    const header = document.querySelector('header');
    const headerOffset = () => header ? header.offsetHeight : 0;
    
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const hash = link.getAttribute('href');
            
            if (!hash || hash === '#') return;
            
            const target = document.querySelector(hash);
            if (!target) return;
            
            e.preventDefault();
            
            const top = target.getBoundingClientRect().top + window.pageYOffset
                - headerOffset() - 10;
            
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });
})();

(function() {
    'use strict';
    
    const btn = document.getElementById('lang-btn');
    const menu = document.getElementById('lang-menu');
    const arrow = document.getElementById('lang-arrow');
    const flag = document.getElementById('lang-flag');
    
    if (!btn || !menu || !arrow || !flag) {
        console.error('Language switcher elements not found');
        return;
    }
    
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isHidden = menu.style.display === 'none' || menu.style.display === '';
        menu.style.display = isHidden ? 'block' : 'none';
        if (arrow) {
            arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    });
    
    menu.addEventListener('click', (e) => {
        const li = e.target.closest('li[data-code]');
        if (!li) return;
        
        const code = li.dataset.code;
        if (!code) return;
        
        const flagText = li.textContent.trim().split(' ')[0];
        if (flag) flag.textContent = flagText;
        
        menu.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
        
        const url = new URL(window.location.href);
        url.searchParams.set('lang', code);
        window.location.href = url.toString();
    });
    
    document.addEventListener('click', (e) => {
        if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
})();

(function() {
    'use strict';
    
    function initSwiper() {
        if (typeof Swiper === 'undefined') {
            console.error('Swiper не найден');
            return;
        }

        const carousel = document.querySelector('.agents-carousel');
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.swiper-slide');
        const slidesCount = slides.length;

        if (window.agentsSwiper && typeof window.agentsSwiper.destroy === 'function') {
            window.agentsSwiper.destroy(true, true);
        }

        window.agentsSwiper = new Swiper('.agents-carousel', {
            loop: slidesCount > 1,
            centeredSlides: true,
            grabCursor: true,
            speed: 700,
            slidesPerView: 1,
            spaceBetween: 24,
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                1200: {
                    slidesPerView: 3,
                    spaceBetween: 40
                },
                1600: {
                    slidesPerView: 3,
                    spaceBetween: 40
                }
            },
            navigation: {
                nextEl: '.agents-nav-next',
                prevEl: '.agents-nav-prev'
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSwiper);
    } else {
        initSwiper();
    }
    
    if (typeof Swiper === 'undefined') {
        const checkSwiper = setInterval(() => {
            if (typeof Swiper !== 'undefined') {
                clearInterval(checkSwiper);
                initSwiper();
            }
        }, 100);
        
        setTimeout(() => clearInterval(checkSwiper), 5000);
    }
})();

(function() {
    var cropper = null;
    var logoFileInput = document.getElementById('logo-file-input');
    var cropperContainer = document.getElementById('logo-cropper-container');
    var cropperImage = document.getElementById('logo-cropper-image');
    var cropBtn = document.getElementById('logo-crop-btn');
    var cancelCropBtn = document.getElementById('logo-cancel-crop-btn');
    var croppedDataInput = document.getElementById('logo-cropped-data');
    var originalFileInput = logoFileInput;

    if (!logoFileInput) return;

    logoFileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        if (!file.type.match('image.*')) {
            alert('Пожалуйста, выберите файл изображения');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            cropperImage.src = e.target.result;
            cropperContainer.style.display = 'block';
            
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                responsive: true,
                ready: function() {
                    cropBtn.style.display = 'inline-block';
                    cancelCropBtn.style.display = 'inline-block';
                }
            });
        };
        reader.readAsDataURL(file);
    });

    if (cropBtn) {
        cropBtn.addEventListener('click', function() {
            if (!cropper) return;

            var canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (canvas) {
                var croppedDataURL = canvas.toDataURL('image/png');
                croppedDataInput.value = croppedDataURL;
                
                cropperContainer.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                
                showCroppedPreview(croppedDataURL);
                
                logoFileInput.value = '';
            }
        });
    }

    if (cancelCropBtn) {
        cancelCropBtn.addEventListener('click', function() {
            cropperContainer.style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            logoFileInput.value = '';
            croppedDataInput.value = '';
        });
    }

    function showCroppedPreview(dataURL) {
        var previewContainer = document.querySelector('.admin-form-image-preview');
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'admin-form-image-preview';
            previewContainer.style.marginTop = '15px';
            logoFileInput.parentNode.appendChild(previewContainer);
        }
        
        var previewText = document.createElement('p');
        previewText.className = 'admin-form-hint';
        previewText.textContent = 'Обрезанный логотип (квадрат):';
        
        var previewImg = document.createElement('img');
        previewImg.src = dataURL;
        previewImg.className = 'admin-image-preview';
        previewImg.style.maxWidth = '200px';
        previewImg.style.maxHeight = '200px';
        previewImg.style.border = '2px solid #3b82f6';
        previewImg.style.borderRadius = '8px';
        
        previewContainer.innerHTML = '';
        previewContainer.appendChild(previewText);
        previewContainer.appendChild(previewImg);
    }

    var form = document.getElementById('project-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (croppedDataInput && croppedDataInput.value) {
                var hiddenInput = document.getElementById('project-logo-base64');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'project-logo-base64';
                    hiddenInput.name = 'Project[logo]';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = croppedDataInput.value;
                
                logoFileInput.value = '';
            }
        });
    }

    function dataURLtoBlob(dataurl) {
        var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {type:mime});
    }
})();

(function() {
    var modal = document.getElementById('screenshot-modal');
    var modalImage = document.getElementById('screenshot-modal-image');
    var modalClose = document.getElementById('screenshot-modal-close');
    var modalPrev = document.getElementById('screenshot-modal-prev');
    var modalNext = document.getElementById('screenshot-modal-next');
    var modalOverlay = document.querySelector('.screenshot-modal-overlay');
    var currentCounter = document.getElementById('screenshot-current');
    var totalCounter = document.getElementById('screenshot-total');
    
    var screenshots = [];
    var currentIndex = 0;
    
    var screenshotItems = document.querySelectorAll('.project-detail-screenshot-item');
    screenshotItems.forEach(function(item) {
        screenshots.push({
            url: item.getAttribute('data-screenshot-url'),
            index: parseInt(item.getAttribute('data-screenshot-index'))
        });
    });
    
    if (screenshots.length === 0) return;
    
    function openModal(index) {
        currentIndex = index;
        updateModalImage();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    function updateModalImage() {
        if (screenshots[currentIndex]) {
            modalImage.src = screenshots[currentIndex].url;
            currentCounter.textContent = currentIndex + 1;
        }
    }
    
    function showPrev() {
        currentIndex = (currentIndex - 1 + screenshots.length) % screenshots.length;
        updateModalImage();
    }
    
    function showNext() {
        currentIndex = (currentIndex + 1) % screenshots.length;
        updateModalImage();
    }
    
    screenshotItems.forEach(function(item, index) {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            openModal(index);
        });
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }
    
    if (modalPrev) {
        modalPrev.addEventListener('click', function(e) {
            e.stopPropagation();
            showPrev();
        });
    }
    
    if (modalNext) {
        modalNext.addEventListener('click', function(e) {
            e.stopPropagation();
            showNext();
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
        if (modal.style.display === 'flex') {
            if (e.key === 'ArrowLeft') {
                showPrev();
            } else if (e.key === 'ArrowRight') {
                showNext();
            }
        }
    });
    
    if (modalImage) {
        modalImage.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    var modalContent = document.querySelector('.screenshot-modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
})();
