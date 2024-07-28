jQuery(document).ready(function ($) {
  if (typeof translationData !== "undefined") {
    const hebrewText = translationData.hebrew;
    const englishText = translationData.english;

    if (hebrewText.length !== englishText.length) {
      console.error("Translation lengths do not match.");
      return;
    }

    // Map Hebrew to English translations
    const translations = {};
    for (let i = 0; i < hebrewText.length; i++) {
      translations[hebrewText[i].trim()] = englishText[i].trim();
    }

    // Function to recursively translate text nodes
    function translateTextNodes(node) {
      if (node.nodeType === Node.TEXT_NODE) {
        const originalText = node.nodeValue.trim();
        if (translations[originalText]) {
          node.nodeValue = translations[originalText];
        }
      } else {
        node.childNodes.forEach(translateTextNodes);
      }
    }

    // Start translation from the body
    translateTextNodes(document.body);
  }
});
