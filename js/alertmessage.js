document.write(
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'
);

function showSuccessText(message) {
  Swal.fire({
    icon: "success",
    title: "Success!",
    text: message,
    confirmButtonColor: "#3085d6",
  });
}

function showErrorText(message) {
  Swal.fire({
    icon: "error",
    title: "Oops...",
    text: message,
    confirmButtonColor: "#d33",
  });
  return false;
}

function showWelcomeText(message) {
  Swal.fire({
    icon: "success",
    title: "Welcome!",
    text: message,
    timer: 2000,
    showConfirmButton: false,
  });
}
