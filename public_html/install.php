<?php
require __DIR__ . '/nm/chrome.php';

$d = nm_shell_data();
nm_shell_open('Install App', 'Install ' . $d['siteTitle'] . ' on your home screen.');
?>
<div style="max-width:480px;margin:32px auto;padding:0 16px">
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;text-align:center">
    <img src="<?php echo nm_h($d['favicon']); ?>" alt="<?php echo nm_h($d['siteTitle']); ?>" width="72" height="72" style="border-radius:14px;margin-bottom:14px;object-fit:contain">
    <h1 style="font-size:22px;margin:0 0 8px;font-weight:700"><?php echo nm_h($d['siteTitle']); ?></h1>
    <p style="color:#6b7280;font-size:14px;line-height:1.5;margin:0 0 22px">Add this site to your phone home screen for faster access.</p>
    <p style="font-size:14px;color:#374151;margin:0;line-height:1.6;text-align:left">
      Android: open Chrome, tap the menu, then <strong>Add to Home screen</strong>.<br><br>
      iPhone: open Safari, tap Share, then <strong>Add to Home Screen</strong>.
    </p>
  </div>
</div>
<?php
nm_shell_close();
