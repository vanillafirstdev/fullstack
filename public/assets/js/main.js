// public/assets/js/main.js — entry module. Browsers resolve these imports themselves.
import "./components/bookmark-card.js";

const button = document.getElementById("refresh-count");
const output = document.getElementById("api-count");

button?.addEventListener("click", async () => {
  try {
    // Relative URL: resolves against <base href>, so subfolders work.
    const response = await fetch("api/bookmarks");
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const bookmarks = await response.json();
    output.textContent = `${bookmarks.length} bookmarks`;
  } catch (error) {
    output.textContent = `Request failed: ${error.message}`;
  }
});
