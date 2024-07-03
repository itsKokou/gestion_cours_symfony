
function redirectToPath(path) {
  fetch(path, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((url) => {
      window.location.href = url;
    })
    .catch((err) => console.log(err));
}

function getData(selectChange) {
  selectChange.addEventListener("change", function (event) {
    const option = event.target.options[selectChange.selectedIndex];
    const path = option.getAttribute("data-path");
    fetch(path, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((url) => {
        window.location.href = url;
      })
      .catch((err) => console.log(err));
  });
}


// function postData(selectChange) {
//   selectChange.addEventListener("change", function (event) {
//     const option = event.target.options[selectChange.selectedIndex];
//     const path = option.getAttribute("data-path");
//     fetch(path, {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/json",
//       },
//     })
//       .then((response) => response.json())
//       .then((url) => {
//         window.location.href = url;
//       })
//       .catch((err) => console.log(err));
//   });
// }

