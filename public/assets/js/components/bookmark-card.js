// bookmark-card.js — identical in the Frontend and Full-Stack Kits.
// Native Custom Element. Builds DOM nodes and sets textContent, never innerHTML
// with attribute values, so a title like <img src=x onerror=alert(1)> renders
// as text, not markup. (The attribute is "label", not "title": title is a
// built-in HTML attribute that would also show as a tooltip.)
//
// A Remove button appears only with the "removable" attribute. The card doesn't
// delete anything itself: it announces "bookmark-remove" and lets the page decide.
// Components that report events, rather than reach into the page's data, stay
// reusable, which is why this file is identical in the Frontend and Full-Stack Kits.
export class BookmarkCard extends HTMLElement {
  connectedCallback() {
    const id = this.getAttribute("bookmark-id") || "";
    const label = this.getAttribute("label") || "Untitled";
    const url = this.getAttribute("url") || "";
    const note = this.getAttribute("note") || "";

    const card = document.createElement("article");
    card.className = "card";

    const heading = document.createElement("h3");
    // Only link http(s) addresses, even if bad data reached storage some other way.
    if (/^https?:\/\//i.test(url)) {
      const link = document.createElement("a");
      link.href = url;
      link.rel = "noopener noreferrer";
      link.textContent = label;
      heading.append(link);
    } else {
      heading.textContent = label;
    }
    card.append(heading);

    if (note) {
      const p = document.createElement("p");
      p.className = "muted";
      p.textContent = note;
      card.append(p);
    }

    const actions = document.createElement("div");
    actions.className = "actions";

    const details = document.createElement("button");
    details.className = "btn btn-ghost";
    details.type = "button";
    details.textContent = "Details";
    details.addEventListener("click", () => this.openDetails(label, url, note));

    actions.append(details);

    if (this.hasAttribute("removable")) {
      const remove = document.createElement("button");
      remove.className = "btn btn-ghost";
      remove.type = "button";
      remove.textContent = "Remove";
      remove.setAttribute("aria-label", `Remove ${label}`);
      remove.addEventListener("click", () => {
        // bubbles: true lets one listener on the list handle every card.
        this.dispatchEvent(new CustomEvent("bookmark-remove", { bubbles: true, detail: { id } }));
      });
      actions.append(remove);
    }

    card.append(actions);
    this.replaceChildren(card);
  }

  // Native <dialog>: focus trapping, Esc to close and a backdrop, no library.
  openDetails(label, url, note) {
    const dialog = document.createElement("dialog");
    const h = document.createElement("h3");
    h.textContent = label;
    const address = document.createElement("p");
    address.className = "break";
    address.textContent = url;
    const p = document.createElement("p");
    p.textContent = note || "No note.";
    const form = document.createElement("form");
    form.method = "dialog";
    const close = document.createElement("button");
    close.className = "btn";
    close.textContent = "Close";
    form.append(close);
    dialog.append(h, address, p, form);
    dialog.addEventListener("close", () => dialog.remove());
    document.body.append(dialog);
    dialog.showModal();
  }
}

customElements.define("bookmark-card", BookmarkCard);
