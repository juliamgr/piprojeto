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
  return ok;
}

continueButton.addEventListener("click", () => {
  if (validateBasic()) setStep(2);
});

backButton.addEventListener("click", () => setStep(1));
cancelButton.addEventListener("click", () => setStep(1));

form.addEventListener("submit", (event) => {
  event.preventDefault();

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

  // Neste ponto os dados estão prontos para serem enviados ao PHP/API.
  // Por enquanto, guardamos uma cópia local para testar o fluxo da interface.
  const data = Object.fromEntries(new FormData(form).entries());
  localStorage.setItem("agrolink_filiacao_teste", JSON.stringify({
    ...data,
    created_at: new Date().toISOString()
  }));

  form.hidden = true;
  successState.hidden = false;
});

document.querySelectorAll("input, select, textarea").forEach(input => {
  input.addEventListener("input", () => clearError(input));
});
