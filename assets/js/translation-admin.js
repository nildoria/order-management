jQuery(document).ready(function ($) {
  $("#add-translation").on("click", function () {
    const translationPair = `
            <div class="translation-pair">
                <input type="text" name="translations[hebrew][]" placeholder="Hebrew Text">
                <input type="text" name="translations[english][]" placeholder="English Translation">
            </div>
        `;
    $("#translations-wrapper").append(translationPair);
  });
});
