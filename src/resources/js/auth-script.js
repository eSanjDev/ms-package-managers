window.AuthScripts = {
    /*** slider ***/
    SliderLogin: function () {
        const slider = document.querySelector('.slider-box-items');
        if (slider) {
            const slides = slider.children;
            const dotsContainer = document.querySelector('.dots');
            const nextBtn = document.querySelector('.btn-next');
            const prevBtn = document.querySelector('.btn-prev');

            let currentIndex = 0;
            const slideCount = slides.length;
            let autoPlayInterval;

            dotsContainer.innerHTML = "";
            for (let i = 0; i < slideCount; i++) {
                const dot = document.createElement("div");
                dot.classList.add("dot");
                if (i === 0) dot.classList.add("active");
                dot.setAttribute("data-index", i);
                dotsContainer.appendChild(dot);

                dot.addEventListener("click", function () {
                    currentIndex = parseInt(this.getAttribute("data-index"));
                    updateSlider();
                    resetAutoplay();
                });
            }

            function updateSlider() {
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
                const dots = dotsContainer.querySelectorAll('.dot');
                dots.forEach((dot, index) => {
                    if (index === currentIndex) {
                        dot.classList.add("active");
                    } else {
                        dot.classList.remove("active");
                    }
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % slideCount;
                updateSlider();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + slideCount) % slideCount;
                updateSlider();
            }

            nextBtn.addEventListener("click", function () {
                nextSlide();
                resetAutoplay();
            });

            prevBtn.addEventListener("click", function () {
                prevSlide();
                resetAutoplay();
            });

            function startAutoplay() {
                autoPlayInterval = setInterval(nextSlide, 4000);
            }

            function resetAutoplay() {
                clearInterval(autoPlayInterval);
                startAutoplay();
            }

            startAutoplay();
        }
    },

    /******************* show and hide password *********/
    showHidePass: function (element) {
        element.classList.toggle('show');
        let input = element.parentElement.querySelector('input');
        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                element.classList.add('active');
            } else {
                input.type = 'password';
                element.classList.remove('active');
            }
        }
    },

    /****************** move next and back input **********/
    initMultiInput: function () {
        const inputs = document.querySelectorAll('.multi-input input');
        if (inputs.length > 1) {
            inputs.forEach((input, index) => {
                input.addEventListener('input', function () {
                    if (this.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && this.value.length === 0) {
                        if (index > 0) {
                            inputs[index - 1].focus();
                        }
                    }
                });
            });
        }
    },

    /********* timer input *******/
    timerForm: function (event, element) {
        if (event) {
            event.preventDefault();
        }
        if (element) {
            let pElements = element.parentElement.querySelectorAll('p');
            pElements.forEach((ptag) => {
                ptag.style.display = "block";
            })
            element.classList.remove('active')
        }
        let timeLeft = 5;
        const timerDisplay = document.querySelector('.text-resend-code .time-password');
        const resendLink = document.querySelector('.text-resend-code a');
        const paragraphs = document.querySelectorAll('.text-resend-code p');

        if (timerDisplay) {
            timerDisplay.textContent = timeLeft;
            const timerInterval = setInterval(() => {
                timeLeft--;
                if (timerDisplay) {
                    timerDisplay.textContent = timeLeft;
                }

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    paragraphs.forEach(p => {
                        p.style.display = 'none';
                    });
                    resendLink.classList.add('active');
                }
            }, 1000);
        }
    },

    /************************* tabs  ***********/
    changeTab: function (event, element) {
        event.preventDefault();
        let tabBox = document.querySelector('.form-tabs');
        if (tabBox) {
            tabBox.querySelectorAll(".form-tabs-title a").forEach(tab => {
                tab.classList.remove("active");
            });

            element.classList.add("active");

            let index = element.getAttribute("data-index");

            tabBox.querySelectorAll(".tab-content").forEach(content => {
                if (content.getAttribute("data-index") === index) {
                    content.classList.add("show");
                } else {
                    content.classList.remove("show");
                }
            });
        }
    },

    /******************* select box ****/
    dropDownBox: function (element) {
        let submenu = element.querySelector('.items-input-select');
        if (submenu) {
            submenu.classList.toggle('show')
            document.addEventListener("click", function (event) {
                if (!element.contains(event.target)) {
                    submenu.classList.remove("show");
                }
            });
        }
    },

    /******** change value select box **/
    changeValueSelect: function (element) {
        let input = element.closest('.select-box').querySelector('input');
        input.value = element.textContent;
    },

    /*************** dropdown footer links **********/
    showSubmen: function (element) {
        let submenu = element.querySelector('.other-link-submenu');
        if (submenu) {
            submenu.classList.toggle('show')
            document.addEventListener("click", function (event) {
                if (!element.contains(event.target)) {
                    submenu.classList.remove("show");
                }
            });
        }
    },

    init: function () {
        this.SliderLogin();
        this.initMultiInput();
        this.timerForm();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    AuthScripts.init();
});
