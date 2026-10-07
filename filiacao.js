const form = document.getElementById("membershipForm");
const basicStep = document.getElementById("basicStep");
const fullStep = document.getElementById("fullStep");
const successState = document.getElementById("successState");
const continueButton = document.getElementById("continueButton");
const backButton = document.getElementById("backButton");
const cancelButton = document.getElementById("cancelButton");
const progressSteps = document.querySelectorAll(".progress-step");

function setStep(step) {
  const first = step === 1;
  basicStep.hidden = !first;
  fullStep.hidden = first;
  progressSteps[0].classList.toggle("current", first);
  progressSteps[1].classList.toggle("current", !first);
  if (!first) progressSteps[1].classList.add("current");
  window.scrollTo({top: 0, behavior: "smooth"});
}

function showError(input, message) {
  const field = input.closest(".field");
  if (!field) return;
  field.classList.add("invalid");
  const error = field.querySelector(".error");
  if (error) error.textContent = message;
}

function clearError(input) {
  const field = input.closest(".field");
  if (!field) return;
  field.classList.remove("invalid");
  const error = field.querySelector(".error");
  if (error) error.textContent = "";
}

function validateBasic() {
  let ok = true;
  const name = document.getElementById("name");
  const email = document.getElementById("email");
  const phone = document.getElementById("phone");
  const privacy = document.getElementById("privacy");

  [name,email,phone].forEach(clearError);

  if (name.value.trim().length < 3) {
    showError(name, "Informe seu nome completo.");
    ok = false;
  }
  if (!email.validity.valid) {
    showError(email, "Informe um e-mail válido.");
    ok = false;
  }
  if (phone.value.replace(/\D/g,"").length < 10) {
    showError(phone, "Informe um telefone válido.");
    ok = false;
  }
  if (!privacy.checked) {
    alert("Você precisa concordar com o uso dos dados para continuar.");
    ok = false;
  }
  const password = document.getElementById("password");
  const confirmation = document.getElementById("password_confirmation");
  [password, confirmation].forEach(clearError);
  const bytes = new TextEncoder().encode(password.value).length;
  if (bytes < 12 || bytes > 72) { showError(password, "Use uma senha de 12 a 72 bytes."); ok = false; }
  if (password.value !== confirmation.value) { showError(confirmation, "As senhas não coincidem."); ok = false; }
  return ok;
}

continueButton.addEventListener("click", () => {
  if (validateBasic()) setStep(2);
});

backButton.addEventListener("click", () => setStep(1));
cancelButton.addEventListener("click", () => setStep(1));

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  if (!validateBasic()) { setStep(1); return; }
  const requiredFull = [...fullStep.querySelectorAll("[required]")];
  let ok = true;

  requiredFull.forEach(input => {
    clearError(input);
    if (!input.value.trim()) {
      showError(input, "Este campo é obrigatório.");
      ok = false;
    }
  });

  if (!ok) {
    fullStep.querySelector(".invalid input, .invalid select")?.focus();
    return;
  }

  const message = document.getElementById("formMessage");
  const button = form.querySelector('[type="submit"]');
  if (button.disabled) return;
  button.disabled = true;
  button.textContent = "Salvando…";
  message.hidden = true;
  try {
    const sessionResponse = await fetch("api/user-session.php", {cache: "no-store"});
    if (!sessionResponse.ok) throw new Error("Não foi possível iniciar a sessão. Acesse o site pelo Apache/PHP.");
    const session = await sessionResponse.json();
    const response = await fetch("api/register.php", {
      method: "POST",
      headers: {"Content-Type": "application/json", "X-CSRF-Token": session.csrf},
      body: JSON.stringify(Object.fromEntries(new FormData(form).entries()))
    });
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || "Não foi possível salvar seu cadastro.");
    try { localStorage.removeItem("agrolink_filiacao_teste"); } catch (_) {}
    form.reset();
    form.hidden = true;
    successState.hidden = false;
  } catch (error) {
    message.textContent = error instanceof SyntaxError ? "Resposta inválida do servidor. Confira o PHP e a conexão com o banco." : error.message;
    message.hidden = false;
    message.scrollIntoView({behavior: "smooth", block: "center"});
  } finally {
    button.disabled = false;
    button.textContent = "Enviar cadastro ✓";
  }
});

document.querySelectorAll("input, select, textarea").forEach(input => {
  input.addEventListener("input", () => clearError(input));
});
