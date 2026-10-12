// validate.js — form checks. Identical in the Frontend and Full-Stack Kits.
//
// The browser already knows the rules written in the HTML (required, maxlength,
// type="url"). checkValidity() asks it; we only add what HTML can't express.

/**
 * Only http(s) addresses. type="url" accepts javascript:alert(1), and a link
 * with that href runs code when clicked. Checking the scheme closes that hole.
 */
export function isWebUrl(value) {
  try {
    const url = new URL(value);
    return url.protocol === "https:" || url.protocol === "http:";
  } catch {
    return false;
  }
}

const MESSAGES = {
  title: "Enter a title of up to 80 characters.",
  url: "Enter a full web address starting with https:// or http://.",
  note: "Keep the note to 200 characters or fewer.",
};

/** Returns { field: message } for every field that fails. Empty object = valid. */
export function validateBookmark(form) {
  const errors = {};
  for (const name of Object.keys(MESSAGES)) {
    const input = form.elements[name];
    input.value = input.value.trim();
    if (!input.checkValidity()) errors[name] = MESSAGES[name];
  }
  if (!errors.url && !isWebUrl(form.elements.url.value)) errors.url = MESSAGES.url;
  return errors;
}

/** Show or clear each field's message, wired for screen readers via aria-describedby. */
export function showErrors(form, errors) {
  for (const name of Object.keys(MESSAGES)) {
    const input = form.elements[name];
    const message = document.getElementById(`${name}-error`);
    if (errors[name]) {
      input.setAttribute("aria-invalid", "true");
      message.textContent = errors[name];
      message.hidden = false;
    } else {
      input.removeAttribute("aria-invalid");
      message.textContent = "";
      message.hidden = true;
    }
  }
  const first = Object.keys(errors)[0];
  if (first) form.elements[first].focus();
}
