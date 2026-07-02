export default function initHomeHeroVideo() {
  var videoDesktop = document.getElementById("hero-desktop-video");
  var videoMobile = document.getElementById("hero-mobile-video");

  if (videoDesktop && videoMobile) {
    // if desktop & mobile videos are found then set the appropriate one to play
    // if there is no mobile video then make sure desktop one plays.

    if (window.innerWidth > 540) {
      //   console.log("here desk");
      videoDesktop.setAttribute("autoplay", "");
      videoDesktop.removeAttribute("preload");
      videoDesktop.play();
    }

    if (window.innerWidth < 540) {
      //   console.log("here mobile");
      videoMobile.setAttribute("autoplay", "");
      videoMobile.removeAttribute("preload");
      videoMobile.play();
    }

    //Window Resize event listener
    var resetVideotimeOutFunctionId;
    window.addEventListener("resize", function () {
      clearTimeout(resetVideotimeOutFunctionId);
      resetVideotimeOutFunctionId = setTimeout(resetVideos, 500);
    });
  }

  function resetVideos() {
    //console.log("resized");
    //alert("resized");

    var videoDesktop = document.getElementById("hero-desktop-video");
    var videoMobile = document.getElementById("hero-mobile-video");
    if (window.innerWidth > 540) {
      //    console.log("here desk play");
      videoDesktop.play();
      videoMobile.pause();
    }

    if (window.innerWidth < 540) {
      //  console.log("here mobile play");
      videoDesktop.pause();
      videoMobile.play();
    }
  }
}
