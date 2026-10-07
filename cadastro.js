(() => {
  "use strict";
  const dialog = document.getElementById("signup-dialog");
  const form = document.getElementById("signup-form");
  const message = document.getElementById("signup-message");
  const success = document.getElementById("signup-success");
  const submit = form.querySelector('[type="submit"]');
  let saving = false;
  document.querySelectorAll("[data-open-signup]").forEach(button => {
    button.addEventListener("click", () => dialog.showModal());
  });
  dialog.querySelector(".signup-close").addEventListener("click", () => dialog.close());
  dialog.addEventListener("click", event => {
    if (event.target !== dialog) return;
    const rect = dialog.getBoundingClientRect();
    if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
  });
  function showError(text) {
    message.textContent = text;
    message.hidden = false;
    message.focus();
  }
  const password = form.elements.namedItem("password");
  const confirmation = form.elements.namedItem("password_confirmation");
  function validatePasswords() {
    const bytes = new TextEncoder().encode(password.value).length;
    password.setCustomValidity(bytes < 12 || bytes > 72 ? "Use uma senha entre 12 e 72 bytes; acentos podem ocupar mais de um byte." : "");
    confirmation.setCustomValidity(password.value !== confirmation.value ? "As senhas não coincidem." : "");
  }
  password.addEventListener("input", validatePasswords);
  confirmation.addEventListener("input", validatePasswords);
  form.addEventListener("submit", async event => {
    event.preventDefault();
    if (saving) return;
    validatePasswords();
    if (!form.reportValidity()) return;
    saving = true;
    submit.disabled = true;
    submit.textContent = "Salvando cadastro…";
    form.setAttribute("aria-busy", "true");
    message.hidden = true;
    const data = Object.fromEntries(new FormData(form).entries());
    try {
      const sessionResponse = await fetch("api/user-session.php", {credentials: "same-origin", cache: "no-store"});
      if (!sessionResponse.ok) throw new Error("Não foi possível iniciar a sessão. Acesse o site pelo Apache do XAMPP.");
      const session = await sessionResponse.json();
      if (!session.csrf) throw new Error("Não foi possível iniciar a sessão. Recarregue a página.");
      const response = await fetch("api/register.php", {
        method: "POST", credentials: "same-origin",
        headers: {"Content-Type": "application/json", "X-CSRF-Token": session.csrf},
        body: JSON.stringify(data)
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || "Não foi possível salvar o cadastro. Tente novamente.");
      form.reset();
      password.setCustomValidity("");
      confirmation.setCustomValidity("");
      form.hidden = true;
      success.hidden = false;
      success.focus();
    } catch (error) {
      showError(error instanceof SyntaxError ? "O servidor não respondeu corretamente. Confira se o Apache e o MySQL estão ligados." : error instanceof TypeError ? "Não foi possível conectar ao servidor. Confira a conexão e tente novamente." : error.message);
    } finally {
      saving = false;
      submit.disabled = false;
      submit.textContent = "Criar minha conta →";
      form.removeAttribute("aria-busy");
    }
  });
})();
