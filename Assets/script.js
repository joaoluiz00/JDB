// No início do script.js
console.log("script.js carregado.");

// Adicionar classe 'loaded' quando a página estiver totalmente carregada
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
    console.log("Página totalmente carregada, classe 'loaded' adicionada");
});

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMContentLoaded disparado.");

    const bgMusic = document.getElementById('bgMusic');
    if (bgMusic) { // Adicionei verificação para garantir que o elemento existe
        bgMusic.loop = true;
        bgMusic.play().catch(error => {
            console.log("Erro ao tentar tocar música automaticamente:", error);
        });
    } else {
        console.warn("Elemento #bgMusic não encontrado.");
    }
});

function openImage(imagePath) {
    const imageViewer = document.getElementById('imageViewer');
    const viewerImage = document.getElementById('viewerImage');
    
    viewerImage.src = imagePath;
    imageViewer.classList.add('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const imageViewer = document.getElementById('imageViewer');
    
    // Adicionar evento de clique em todas as imagens
    document.querySelectorAll('.card-image').forEach(img => {
        img.onclick = function() {
            openImage(this.src);
        }
    });

    // Fechar ao clicar no X
    document.querySelector('.close').onclick = function() {
        imageViewer.classList.remove('show');
    }

    // Fechar ao clicar fora da imagem
    imageViewer.onclick = function(event) {
        if (event.target === imageViewer) {
            imageViewer.classList.remove('show');
        }
    }

    // Fechar com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            imageViewer.classList.remove('show');
        }
    });
});

// Efeitos dos Botões
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('mousedown', () => {
        button.style.transform = 'translateY(4px)';
    });
    
    button.addEventListener('mouseup', () => {
        button.style.transform = 'translateY(0)';
    });
});

// Controle do Formulário (Correção dos IDs) - Verifique se 'loginForm' existe em todas as páginas que usam este script
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) { // Verifica se o formulário existe na página
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            console.log('Dados do Formulário:', Object.fromEntries(formData));
            this.submit();
        });
    }
});


function toggleTheme() {
    const html = document.documentElement;
    const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    const logo = document.getElementById('themeLogo');
    
    // Alternar tema
    html.setAttribute('data-theme', newTheme);
    
    // Alternar logo (apenas se o logo existir)
    if (logo) {
        logo.src = newTheme === 'dark' 
            ? '../Assets/img/logofoto2.png' 
            : '../Assets/img/logofoto1.png';
    }
    
    // Trocar ícones dos cards conforme o tema
    document.querySelectorAll('.theme-icon-card').forEach(icon => {
        const lightSrc = icon.getAttribute('data-light');
        const darkSrc = icon.getAttribute('data-dark');
        icon.src = newTheme === 'dark' ? darkSrc : lightSrc;
    });
    
    localStorage.setItem('theme', newTheme);

    // Atualizar visibilidade dos ícones de tema
    document.querySelectorAll('.theme-icon').forEach(icon => {
        icon.style.display = icon.classList.contains(newTheme + '-icon') ? 'none' : 'block';
    });
}

// Carregar tema salvo
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const logo = document.getElementById('themeLogo');
    const html = document.documentElement;

    // Aplicar tema salvo
    html.setAttribute('data-theme', savedTheme);
    
    // Atualizar logo se existir
    if (logo) {
        logo.src = savedTheme === 'dark' 
            ? '../Assets/img/logofoto2.png' 
            : '../Assets/img/logofoto1.png';
    }

    // Aplicar ícones dos cards conforme o tema salvo
    document.querySelectorAll('.theme-icon-card').forEach(icon => {
        const lightSrc = icon.getAttribute('data-light');
        const darkSrc = icon.getAttribute('data-dark');
        icon.src = savedTheme === 'dark' ? darkSrc : lightSrc;
    });

    // Atualizar ícones do alternador de tema
    document.querySelectorAll('.theme-icon').forEach(icon => {
        if (savedTheme === 'dark') {
            icon.style.display = icon.classList.contains('light-icon') ? 'block' : 'none';
        } else {
            icon.style.display = icon.classList.contains('dark-icon') ? 'block' : 'none';
        }
    });
});

// Forçar tema no load para garantir que a imagem de fundo seja carregada
window.addEventListener('load', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
});