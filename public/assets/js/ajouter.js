const form = document.querySelector("form");
const titleInput = document.getElementById("title");
const linkInput = document.getElementById("link");
const techlinkInput = document.getElementById("techlink");
const imageUrlInput = document.getElementById("image-url");
const imageFileInput = document.getElementById("image-file");

// Génération automatique du techlink à partir du lien Google Sheets
linkInput.addEventListener("input", function () {
  const linkValue = linkInput.value;
  const regex =
    /https:\/\/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/;

  const match = linkValue.match(regex);
  if (match) {
    const docId = match[1];
    const previewLink = `https://docs.google.com/spreadsheets/d/${docId}/preview`;
    techlinkInput.value = previewLink;
  } else {
    techlinkInput.value = ""; // Réinitialise si le lien n'est pas valide
  }
});

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const title = titleInput.value.trim();
  const link = linkInput.value.trim();
  const techlink = techlinkInput.value.trim();
  const imageUrl = imageUrlInput.value.trim();
  const imageFile = imageFileInput.files[0];

  // Validation des champs
  if (!title || !link) {
    alert("Les champs titre et lien sont obligatoires.");
    return;
  }

  if (!techlink) {
    alert("Le lien technique est obligatoire.");
    return;
  }

  // Gestion de l'image (URL ou fichier)
  if (imageUrl) {
    saveCard(title, link, techlink, imageUrl);
  } else if (imageFile) {
    const reader = new FileReader();
    reader.onload = function (event) {
      saveCard(title, link, techlink, event.target.result);
    };
    reader.readAsDataURL(imageFile);
  } else {
    alert("Veuillez fournir une image (URL ou fichier).");
  }
});

function saveCard(title, link, techlink, imageSrc) {
  const newCard = { title, link, techlink, imageSrc };

  fetch("https://6809e4101f1a52874cde3ce7.mockapi.io/documents", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(newCard),
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error("Erreur lors de l'envoi vers MockAPI");
      }
      return res.json();
    })
    .then(() => {
      alert("Document ajouté dans la base !");
      form.reset(); // Réinitialiser le formulaire après succès
    })
    .catch((error) => {
      console.error("Erreur :", error);
      alert("Une erreur est survenue lors de l'ajout.");
    });
}
