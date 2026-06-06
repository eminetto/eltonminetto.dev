// Adiciona botão "copiar" a cada bloco de código (.highlight).
(function () {
  "use strict";
  function init() {
    var root = document.documentElement;
    var labelCopy = root.getAttribute("data-copy-label") || "copy";
    var labelDone = root.getAttribute("data-copied-label") || "copied!";
    var blocks = document.querySelectorAll("#content .highlight");

    blocks.forEach(function (block) {
      block.classList.add("code-wrap");
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "copy-btn";
      btn.textContent = labelCopy;
      btn.setAttribute("aria-label", labelCopy);
      btn.addEventListener("click", function () {
        var code = block.querySelector("code");
        var text = code ? code.innerText : block.innerText;
        navigator.clipboard.writeText(text).then(function () {
          btn.textContent = labelDone;
          setTimeout(function () {
            btn.textContent = labelCopy;
          }, 1800);
        });
      });
      block.appendChild(btn);
    });
  }
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
