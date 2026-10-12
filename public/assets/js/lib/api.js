// assets/js/lib/api.js — the bookmarks API, called with fetch().
//
// This does the Frontend Kit's store.js job, with one difference that changes
// everything: the data lives in SQLite on the server, so every visitor sees
// the same list, and the server checks every rule again. Anything in the
// browser can be bypassed; the server's checks can't.
//
// Every call is async, because each one is a network round trip that can fail.

// The layout prints the session's CSRF token in a <meta> tag. Writes send it
// back in a header, which proves the request came from one of our own pages.
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? "";

/** A failed request, carrying the server's message and any per-field errors. */
export class ApiError extends Error {
  constructor(status, body) {
    super(body?.error ?? `The server answered ${status}.`);
    this.status = status;
    this.fields = body?.fields ?? {};
  }
}

async function request(path, { method = "GET", data } = {}) {
  const headers = { Accept: "application/json" };
  if (method !== "GET") headers["X-CSRF-Token"] = csrfToken;
  if (data !== undefined) headers["Content-Type"] = "application/json";

  // Relative path: resolves against <base href>, so subfolders work.
  const response = await fetch(path, {
    method,
    headers,
    body: data === undefined ? undefined : JSON.stringify(data),
  });

  // 204 No Content: success with nothing to read.
  if (response.status === 204) return null;

  const body = await response.json().catch(() => null);
  if (!response.ok) throw new ApiError(response.status, body);
  return body;
}

export function getBookmarks() {
  return request("api/bookmarks");
}

export function addBookmark({ title, url, note }) {
  return request("api/bookmarks", { method: "POST", data: { title, url, note } });
}

export function removeBookmark(id) {
  return request(`api/bookmarks/${encodeURIComponent(id)}`, { method: "DELETE" });
}
