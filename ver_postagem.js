document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('id');
    
    if (postId) {
        carregarPostagem(postId);
    } else {
        window.location.href = 'postagens.html';
    }
});

async function carregarPostagem(id) {
    try {
        const response = await fetch(`index.php?rota=postagem&id=${id}`);
        const postagem = await response.json();
        
        const container = document.getElementById('postagem-container');
        container.innerHTML = `
            <article class="postagem-detalhe">
                <h2>${postagem.titulo}</h2>
                <div class="conteudo">${postagem.conteudo}</div>
                <div class="meta">
                    <span>Autor: ${postagem.autor}</span>
                    <span>Data: ${new Date(postagem.created_at).toLocaleString()}</span>
                </div>
                
                ${postagem.usuario_id === usuarioLogado.id ? `
                    <div class="acoes">
                        <a href="editar_postagem.html?id=${postagem.id}" class="btn-editar">Editar</a>
                        <button class="btn-excluir" data-id="${postagem.id}">Excluir</button>
                    </div>
                ` : ''}
            </article>
        `;
        
        // Adicionar evento para o botão excluir
        const btnExcluir = container.querySelector('.btn-excluir');
        if (btnExcluir) {
            btnExcluir.addEventListener('click', () => excluirPostagem(postagem.id));
        }
    } catch (error) {
        console.error('Erro ao carregar postagem:', error);
    }
}

async function excluirPostagem(id) {
    if (!confirm('Tem certeza que deseja excluir esta postagem?')) return;
    
    try {
        const response = await fetch(`index.php?rota=excluir_postagem&id=${id}`, {
            method: 'DELETE'
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            window.location.href = 'postagens.html';
        } else {
            alert(resultado.error || 'Erro ao excluir postagem');
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}