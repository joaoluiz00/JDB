document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Aqui você pode adicionar a lógica para enviar os dados para o servidor
    console.log('Email:', email);
    console.log('Senha:', password);

    // Simulação de envio de formulário
    this.submit();
});

document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Aqui você pode adicionar a lógica para enviar os dados para o servidor
    console.log('Nome:', nome);
    console.log('Email:', email);
    console.log('Senha:', password);

    // Simulação de envio de formulário
    this.submit();
});