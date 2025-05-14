document.getElementById("user-name").textContent = "Utilisateur";

document.getElementById("logout-btn").addEventListener("click", (e) => {
  e.preventDefault();
  // Clear localStorage on logout
  localStorage.clear();
  window.location.href = "index.html";
});

window.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".preview-container");

  fetch("https://6809e4101f1a52874cde3ce7.mockapi.io/documents")
    .then((res) => res.json())
    .then((cards) => {
      if (cards.length === 0) {
        container.innerHTML =
          "<p style='text-align:center;'>Aucun document disponible.</p>";
        return;
      }

      cards.forEach(({ id, title, link, imageSrc }) => {
        const cardContainer = document.createElement("div");
        cardContainer.className = "card-container";

        const card = document.createElement("a");
        card.className = "preview-card";
        card.href = link;
        card.target = "_blank";

        const img = document.createElement("img");
        img.src = imageSrc;
        img.alt = title;

        const titleDiv = document.createElement("div");
        titleDiv.className = "card-title";
        titleDiv.textContent = title;

        card.appendChild(img);
        card.appendChild(titleDiv);

        const editBtn = document.createElement("button");
        editBtn.textContent = "Modifier";
        editBtn.addEventListener("click", () => {
          openEditForm(id, title, imageSrc, link);
        });

        cardContainer.appendChild(card);
        cardContainer.appendChild(editBtn);
        container.appendChild(cardContainer);
      });
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des cartes :", error);
    });
});

function openEditForm(id, title, imageSrc, link) {
  document.getElementById("edit-id").value = id;
  document.getElementById("edit-title").value = title;
  document.getElementById("edit-image").value = imageSrc;
  document.getElementById("edit-link").value = link;
  document.getElementById("edit-section").style.display = "flex";
  window.scrollTo(0, document.body.scrollHeight);
}

function cancelEdit() {
  document.getElementById("edit-section").style.display = "none";
}

function submitEdit() {
  const id = document.getElementById("edit-id").value;
  const newTitle = document.getElementById("edit-title").value;
  const newImage = document.getElementById("edit-image").value;
  const newLink = document.getElementById("edit-link").value;

  fetch(`https://6809e4101f1a52874cde3ce7.mockapi.io/documents/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      title: newTitle,
      imageSrc: newImage,
      link: newLink,
    }),
  })
    .then((res) => res.json())
    .then(() => {
      alert("Carte modifiée !");
      location.reload();
    })
    .catch((err) => {
      console.error("Erreur lors de la modification :", err);
    });
}
