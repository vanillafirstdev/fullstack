// assets/js/client.js — entry module for the /client page.
//
// Compare it with the Frontend Kit's main.js. The component and validate.js are
// the same files; store.js (localStorage) became lib/api.js (fetch), and every
// change now waits for the server before the list changes.
import "./components/bookmark-card.js";
import { getBookmarks, addBookmark, removeBookmark, ApiError } from "./lib/api.js";
import { validateBookmark, showErrors } from "./lib/validate.js";

const form = document.getElementById("bookmark-form");
const list = document.getElementById("bookmark-list");
const count = document.getElementById("bookmark-count");
const status = document.getElementById("app-status");
const saveButton = form.querySelector('button[type="submit"]');

let bookmarks = [];

function render() {
  const cards = bookmarks.map((bookmark) => {
    const card = document.createElement("bookmark-card");
    card.setAttribute("bookmark-id", String(bookmark.id));
    card.setAttribute("label", bookmark.title);
    card.setAttribute("url", bookmark.url);
    card.setAttribute("note", bookmark.note || "");
    card.setAttribute("removable", "");
    return card;
  });

  if (cards.length === 0) {
    const empty = document.createElement("p");
    empty.className = "muted";
    empty.textContent = "No bookmarks yet. Add one above.";
    cards.push(empty);
  }

  list.replaceChildren(...cards);
  count.textContent = `(${bookmarks.length})`;
}

function say(message) {
  status.textContent = message;
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  // Check in the browser first: instant feedback, no round trip.
  const errors = validateBookmark(form);
  showErrors(form, errors);
  if (Object.keys(errors).length > 0) return;

  // Stop double submissions while the request is in flight.
  saveButton.disabled = true;
  say("Saving…");
  try {
    const created = await addBookmark({
      title: form.elements.title.value,
      url: form.elements.url.value,
      note: form.elements.note.value,
    });
    bookmarks = [created, ...bookmarks];
    render();
    form.reset();
    form.elements.title.focus();
    say(`Saved "${created.title}".`);
  } catch (error) {
    // 422: the server's rules said no. Its messages go beside the same fields.
    if (error instanceof ApiError && error.status === 422) {
      showErrors(form, error.fields);
      say(error.message);
    } else {
      say(`Not saved: ${error.message}`);
    }
  } finally {
    saveButton.disabled = false;
  }
});

// One listener for every card's Remove button (event delegation).
list.addEventListener("bookmark-remove", async (event) => {
  const id = event.detail.id;
  try {
    await removeBookmark(id);
    bookmarks = bookmarks.filter((bookmark) => String(bookmark.id) !== id);
    render();
    say("Removed.");
  } catch (error) {
    say(`Not removed: ${error.message}`);
  }
});

try {
  bookmarks = await getBookmarks();
  render();
} catch (error) {
  const message = document.createElement("p");
  message.className = "notice";
  message.textContent = `Couldn't load bookmarks: ${error.message}`;
  list.replaceChildren(message);
}
