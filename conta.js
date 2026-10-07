(() => {
    const link = document.getElementById("cliente-conta");
  
    if (!link) return;
  
    async function atualizarConta() {
      try {
        const response = await fetch("api/cliente-sessao.php", {
          credentials: "same-origin",
          cache: "no-store"
        });
  
        if (!response.ok) return;
  
        const session = await response.json();
  
        link.href = "conta.php";
  
        if (session.loggedIn) {
          const firstName = String(session.name || "")
            .trim()
            .split(/\s+/)[0];
  
          link.textContent = firstName
            ? `Olá, ${firstName} · Minha conta`
            : "Minha conta";
        } else {
          link.textContent = "Entrar na minha conta";
        }
  
        document.querySelectorAll("[data-open-signup]").forEach(button => {
          button.hidden = session.loggedIn;
          button.style.display = session.loggedIn ? "none" : "";
        });
      } catch (error) {
        console.error("Não foi possível consultar a sessão do cliente.");
      }
    }
  
    window.addEventListener("pageshow", atualizarConta);
  })();