function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
  }
  
  function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = "expires=" + d.toUTCString();
    document.cookie = `${name}=${value}; ${expires}; path=/`;
  }
  
  document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("cookie-popup");
    const acceptBtn = document.getElementById("accept-cookies");
    const declineBtn = document.getElementById("decline-cookies");
  
    if (!getCookie("cookieConsent")) {
      popup.style.display = "block";
    }
  
    acceptBtn.addEventListener("click", () => {
      setCookie("cookieConsent", "accepted", 30);
      popup.style.display = "none";
    });
  
    declineBtn.addEventListener("click", () => {
      setCookie("cookieConsent", "declined", 30);
      popup.style.display = "none";
    });
  });