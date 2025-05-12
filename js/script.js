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

  var initChocolat = function () {
    Chocolat(document.querySelectorAll(".image-link"), {
      imageSize: "contain",
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
      .then((res) => res.json())
      .then((data) => {
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
                toastBody.textContent =
                  "Please log in as a landlord to add a property.";
                toast.show();
              }
            });
          }
        }
      })
      .catch((err) => console.error("User fetch error:", err));

    // Add property access check
    if (addBtn) {
      addBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const toastEl = document.getElementById("loginToast");
        const toastBody = document.getElementById("loginToastBody");
        const toast = new bootstrap.Toast(toastEl);

        fetch("LogSign/project/get_user.php")
          .then((res) => res.json())
          .then((data) => {
            if (
              data.loggedIn &&
              (data.role === "landlord" || data.role === "both")
            ) {
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
              toast.show();
            }
          })
          .catch((err) => {
            console.error("User fetch error:", err);
            toastBody.textContent = "Unable to verify user. Please log in.";
            toast.show();
          });
      });
    }

document.addEventListener("DOMContentLoaded", () => {
  const accountModal = document.getElementById("accountModal");
  const form = document.getElementById("accountUpdateForm");
  const updateMsg = document.getElementById("updateMessage");

  // Load user data into modal on open
 accountModal.addEventListener("shown.bs.modal", () => {
  fetch("LogSign/project/get_user.php")
    .then(res => res.json())
    .then(data => {
      if (data.loggedIn) {
        document.getElementById("modal-username").value = data.username || "";
        document.getElementById("modal-email").value = data.email || "";
        document.getElementById("modal-role").value = data.role || "tenant";
        document.getElementById("avatarPreview").src = data.avatar || "images/user-avatar.png";
      }
    })
    .catch(err => console.error("Error loading user info:", err));
});

  // Toggle password visibility
  document.getElementById("togglePassword").addEventListener("click", () => {
    const pwd = document.getElementById("modal-password");
    const icon = document.querySelector("#togglePassword i");
    if (pwd.type === "password") {
      pwd.type = "text";
      icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
      pwd.type = "password";
      icon.classList.replace("bi-eye-slash", "bi-eye");
    }
  });

  // Preview avatar
  document.getElementById("avatar").addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById("avatarPreview").src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  // Submit profile update
  form.addEventListener("submit", e => {
    e.preventDefault();
    updateMsg.textContent = "";

    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }

    const formData = new FormData(form);
    const avatarFile = document.getElementById("avatar").files[0];
    if (avatarFile) formData.append("avatar", avatarFile);

    fetch("LogSign/project/update_user.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(msg => {
        updateMsg.textContent = msg;
        updateMsg.className = "text-success";

        // Update navbar
        document.getElementById("username").textContent = formData.get("username");
        document.getElementById("account-email").textContent = formData.get("email");
        document.querySelectorAll(".user-avatar").forEach(img => {
          img.src = document.getElementById("avatarPreview").src;
        });

        setTimeout(() => location.reload(), 1500);
      })
      .catch(err => {
        updateMsg.textContent = "Error updating profile.";
        updateMsg.className = "text-danger";
      });
  });
});



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
      },
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
      effect: "fade",
      thumbs: {
        swiper: thumb_slider,
      },
    });

    initChocolat();
  });
})(jQuery);
