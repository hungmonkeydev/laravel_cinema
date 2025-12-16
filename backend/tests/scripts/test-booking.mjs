import { decode } from "querystring";

const BASE = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000";
function parseSetCookie(setCookie) {
  // setCookie may be an array or a single string
  const arr = Array.isArray(setCookie) ? setCookie : [setCookie];
  const cookies = {};
  for (const line of arr) {
    const parts = line.split(/;\s*/);
    const [name, ...val] = parts[0].split("=");
    cookies[name] = val.join("=");
  }
  return cookies;
}

(async function main() {
  try {
    console.log("GET /sanctum/csrf-cookie");
    const res = await fetch(`${BASE}/sanctum/csrf-cookie`, { method: "GET" });
    const raw = res.headers.get("set-cookie");
    if (!raw) {
      console.error("No set-cookie header from CSRF endpoint.");
      process.exit(1);
    }
    const cookies = parseSetCookie(raw);
    const xsrf = cookies["XSRF-TOKEN"];
    if (!xsrf) {
      console.error("XSRF-TOKEN not found in cookies:", cookies);
      process.exit(1);
    }
    // XSRF-TOKEN is URL-encoded; decode it
    const xsrfDecoded = decodeURIComponent(xsrf);

    const cookieHeader = Object.entries(cookies)
      .map(([k, v]) => `${k}=${v}`)
      .join("; ");

    console.log("Posting booking create with cookie and X-XSRF-TOKEN");
    const payload = {
      user_id: 1,
      showtime_id: 1,
      seat_ids: [1],
      payment_method: "vnpay",
    };

    const postRes = await fetch(`${BASE}/api/booking/create`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Cookie: cookieHeader,
        "X-XSRF-TOKEN": xsrfDecoded,
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
      redirect: "manual",
    });

    console.log("Status:", postRes.status);
    const text = await postRes.text();
    console.log("Response body:", text.slice(0, 1000));
    if (postRes.status === 200 || postRes.status === 201) {
      try {
        console.log("JSON:", await postRes.json());
      } catch (e) {}
    }
  } catch (e) {
    console.error("Error:", e);
    process.exit(1);
  }
})();
