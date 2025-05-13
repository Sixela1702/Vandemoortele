const form = document.querySelector("form");
const titleInput = document.getElementById("title");
const linkInput = document.getElementById("link");
const imageUrlInput = document.getElementById("image-url");
const imageFileInput = document.getElementById("image-file");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const title = titleInput.value;
  const link = linkInput.value;
  const imageUrl = imageUrlInput.value;
  const imageFile = imageFileInput.files[0];

  if (imageUrl) {
    saveCard(title, link, imageUrl);
  } else if (imageFile) {
    const reader = new FileReader();
    reader.onload = function (event) {
      saveCard(title, link, event.target.result);
    };
    reader.readAsDataURL(imageFile);
  } else {
    alert("Veuillez fournir une image.");
  }

  form.reset();
});

function saveCard(title, link, imageSrc) {
  const newCard = { title, link, imageSrc };

  // Envoi vers Sheet.best
  fetch("https://6809e4101f1a52874cde3ce7.mockapi.io/documents", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(newCard),
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error("Erreur lors de l'envoi vers Sheet.best");
      }
      return res.json();
    })
    .then(() => {
      alert("Document ajouté dans Google Sheet !");
    })
    .catch((error) => {
      console.error("Erreur :", error);
      alert("Une erreur est survenue lors de l'ajout.");
    });
}
