<?php /** @var array $links */ /** @var string $csrf */ /** @var array $flash */ /** @var string $base */ ?>
<div class="card">
  <h2>🔗 PHP Shorty</h2>

  <?php if (!empty($flash)): ?>
    <div class="flash-wrap">
      <?php foreach ($flash as $f): ?>
        <div class="flash <?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form class="row add" method="post" action="/create" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <input class="grow" name="url" placeholder="Paste a long URL (https://…)" autofocus>
    <input class="code" name="code" placeholder="custom-code (optional)">
    <button>Create</button>
  </form>

  <?php if (empty($links)): ?>
    <p class="empty">No links yet. Create your first short link above 👆</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr><th>Short</th><th>Destination</th><th>Clicks</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($links as $l): ?>
          <tr>
            <td><a target="_blank" href="/r/<?= htmlspecialchars($l['code']) ?>"><?= $base ?>/r/<?= htmlspecialchars($l['code']) ?></a></td>
            <td class="truncate" title="<?= htmlspecialchars($l['url']) ?>"><?= htmlspecialchars($l['url']) ?></td>
            <td class="num"><?= (int)$l['clicks'] ?></td>
            <td>
              <form method="post" action="/delete" onsubmit="return confirm('Delete this short link?')">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($l['id']) ?>">
                <button class="danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
