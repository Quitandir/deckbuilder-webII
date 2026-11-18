<?php // componente que agrupa as mensagens de sessão (erro, sucesso, aviso) para exibir nas telas ?>
<?php if (!empty($erro)): ?>
    <p role="alert" class="mensagem-erro"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
    <p role="status" class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></p>
<?php endif; ?>
<?php if (!empty($aviso)): ?>
    <p role="status" class="mensagem-aviso"><?= htmlspecialchars($aviso) ?></p>
<?php endif; ?>
