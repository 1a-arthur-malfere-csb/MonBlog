class ToastManager {
  constructor() {
    this.container = this.createContainer();
    this.toasts = [];
    document.body.appendChild(this.container);
  }

  createContainer() {
    const container = document.createElement("div");
    container.className = "toast-container";
    container.id = "toast-container";
    return container;
  }

  show(message, type = "info", title = null, duration = 5000) {
    const toast = this.createToast(message, type, title);
    this.container.appendChild(toast);
    this.toasts.push(toast);

    setTimeout(() => {
      toast.classList.add("toast-show");
    }, 10);

    if (duration > 0) {
      setTimeout(() => {
        this.hide(toast);
      }, duration);
    }

    return toast;
  }

  createToast(message, type, title) {
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const icon = this.getIcon(type);

    toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${this.escapeHtml(title)}</div>` : ""}
                <div class="toast-message">${this.escapeHtml(message)}</div>
            </div>
            <button class="toast-close" aria-label="Fermer">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M12.5 3.5L8 8 3.5 3.5 2.5 4.5 7 9l-4.5 4.5 1 1L8 10l4.5 4.5 1-1L9 9l4.5-4.5z"/>
                </svg>
            </button>
        `;

    const closeBtn = toast.querySelector(".toast-close");
    closeBtn.addEventListener("click", () => {
      this.hide(toast);
    });

    toast.addEventListener("click", (e) => {
      if (e.target === toast || e.target.closest(".toast-content")) {
        this.hide(toast);
      }
    });

    return toast;
  }

  getIcon(type) {
    const icons = {
      success: `<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm-1 15l-5-5 1.414-1.414L9 12.172l5.586-5.586L16 8l-7 7z"/>
            </svg>`,
      error: `<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm5 13.59L13.59 15 10 11.41 6.41 15 5 13.59 8.59 10 5 6.41 6.41 5 10 8.59 13.59 5 15 6.41 11.41 10 15 13.59z"/>
            </svg>`,
      warning: `<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm-1 4h2v6H9V4zm0 8h2v2H9v-2z"/>
            </svg>`,
      info: `<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm-1 4h2v2H9V4zm0 4h2v8H9V8z"/>
            </svg>`,
    };
    return icons[type] || icons.info;
  }

  hide(toast) {
    toast.classList.add("toast-hide");
    toast.classList.remove("toast-show");

    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
      const index = this.toasts.indexOf(toast);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
    }, 300);
  }

  hideAll() {
    this.toasts.forEach((toast) => {
      this.hide(toast);
    });
  }

  showSuccess(message, title = "Succès") {
    return this.show(message, "success", title);
  }

  showError(message, title = "Erreur") {
    return this.show(message, "error", title, 7000);
  }

  showWarning(message, title = "Attention") {
    return this.show(message, "warning", title, 6000);
  }

  showInfo(message, title = "Information") {
    return this.show(message, "info", title);
  }

  // Utility method to escape HTML
  escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
}

let toastManager;

document.addEventListener("DOMContentLoaded", function () {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
});

if (typeof module !== "undefined" && module.exports) {
  module.exports = ToastManager;
}

function showToast(message, type = "info", title = null, duration = 5000) {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
  return toastManager.show(message, type, title, duration);
}

function showSuccessToast(message, title = "Succès") {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
  return toastManager.showSuccess(message, title);
}

function showErrorToast(message, title = "Erreur") {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
  return toastManager.showError(message, title);
}

function showWarningToast(message, title = "Attention") {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
  return toastManager.showWarning(message, title);
}

function showInfoToast(message, title = "Information") {
  if (!toastManager) {
    toastManager = new ToastManager();
  }
  return toastManager.showInfo(message, title);
}
