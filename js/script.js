(function ($) {
  "use strict";

  const rangeInput = document.querySelectorAll(".range-input input"),
        priceInput = document.querySelectorAll(".price-input input"),
        range = document.querySelector(".slider .progress");
  let priceGap = 1000;

  priceInput.forEach((input) => {
    input.addEventListener("input", (e) => {
      let minPrice = parseInt(priceInput[0].value),
          maxPrice = parseInt(priceInput[1].value);

      if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
        if (e.target.className === "input-min") {
          rangeInput[0].value = minPrice;
          range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
        } else {
          rangeInput[1].value = maxPrice;
          range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
        }
      }
    });
  });

  rangeInput.forEach((input) => {
    input.addEventListener("input", (e) => {
      let minVal = parseInt(rangeInput[0].value),
          maxVal = parseInt(rangeInput[1].value);

      if (maxVal - minVal < priceGap) {
        if (e.target.className === "range-min") {
          rangeInput[0].value = maxVal - priceGap;
        } else {
          rangeInput[1].value = minVal + priceGap;
        }
      } else {
        priceInput[0].value = minVal;
        priceInput[1].value = maxVal;
        range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
        range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
      }
    });
  });
document.addEventListener("DOMContentLoaded", () => {
  fetch("LogSign/project/get_user.php") // adjust path if needed
    .then(res => res.json())
    .then(data => {
      if (data.loggedIn) {
        const usernameEl = document.getElementById("username");
        if (usernameEl) {
          usernameEl.textContent = data.username;
        }
      }
    })
    .catch(err => {
      console.error("Error fetching user data:", err);
    });
});
  var initChocolat = function () {
    Chocolat(document.querySelectorAll('.image-link'), {
      imageSize: 'contain',
      loop: true,
    });
  };

  window.addEventListener("scroll", function () {
    const header = document.querySelector(".site-header");
    if (window.innerWidth >= 992) {
      if (window.scrollY > 10) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    }
  });

  $(document).ready(function () {
    const addBtn = document.getElementById("addPropertyBtn");
    const logoutBtn = document.getElementById("logoutBtn");
    const usernameSpan = document.getElementById("username");
    fetch("LogSign/project/get_user.php")
  .then(res => res.json())
  .then(data => {
    if (data.loggedIn && usernameSpan) {
      usernameSpan.textContent = data.username;

      // Handle Add Property logic
      if (addBtn) {
        addBtn.addEventListener("click", function (e) {
          e.preventDefault();
          const toastEl = document.getElementById("loginToast");
          const toastBody = document.getElementById("loginToastBody");
          const toast = new bootstrap.Toast(toastEl);

          if (data.role === "landlord" || data.role === "both") {
            toastBody.textContent = "Redirecting to add property...";
            toast.show();
            setTimeout(() => {
              toast.hide();
              window.location.href = "landlord.html";
            }, 1500);
          } else {
            toastBody.textContent = "Please log in as a landlord to add a property.";
            toast.show();
          }
        });
      }
    }
  })
  .catch(err => console.error("User fetch error:", err));


    // Add property access check
    if (addBtn) {
      addBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const toastEl = document.getElementById("loginToast");
        const toastBody = document.getElementById("loginToastBody");
        const toast = new bootstrap.Toast(toastEl);

        if (currentUser && (currentUser.role === "landlord" || currentUser.role === "both")) {
          toastBody.textContent = "Redirecting to add property...";
          toast.show();
          setTimeout(() => {
            toast.hide();
            window.location.href = "landlord.html";
          }, 1500);
        } else {
         toastBody.innerHTML = `
  You are currently a <strong>${data.role}</strong>.<br>
  <a href="../groupE/LogSign/Project/Signup.html" class="text-white fw-bold text-decoration-underline">create landlord profile</a>.
`;
        }
      });
    }

    // Logout
    if (logoutBtn) {
      logoutBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const toastEl = document.getElementById("logoutToast");
        const toastBody = document.getElementById("logoutToastBody");
        const toast = new bootstrap.Toast(toastEl);

        toastBody.textContent = "Logging out...";
        toast.show();

        localStorage.removeItem("user");
        setTimeout(() => {
          toastBody.textContent = "Logged out successfully!";
        }, 1200);

        setTimeout(() => {
          toast.hide();
          window.location.href = "landingpage.html";
        }, 2500);
      });
    }

    // Initialize Swipers
    new Swiper(".residence-swiper", {
      slidesPerView: 3,
      spaceBetween: 30,
      freeMode: true,
      navigation: {
        nextEl: ".residence-swiper-next",
        prevEl: ".residence-swiper-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        300: { slidesPerView: 1, spaceBetween: 20 },
        768: { slidesPerView: 2, spaceBetween: 30 },
        1024: { slidesPerView: 3, spaceBetween: 30 },
      }
    });

    new Swiper(".testimonial-swiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      freeMode: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
    });

    const thumb_slider = new Swiper(".product-thumbnail-slider", {
      autoplay: true,
      loop: true,
      spaceBetween: 8,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
    });

    new Swiper(".product-large-slider", {
      autoplay: true,
      loop: true,
      spaceBetween: 10,
      effect: 'fade',
      thumbs: {
        swiper: thumb_slider,
      },
    });

    initChocolat();
  });

})(jQuery);
