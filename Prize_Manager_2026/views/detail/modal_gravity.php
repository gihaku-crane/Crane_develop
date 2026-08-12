<!--
* モーダル自体のHTML構造（背景、枠、閉じるボタンなど）
-->
<div id="gravityModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>重心情報を設定</h3>
        
        <?php include 'gravity_picker_ui.php'; ?>
        
        <button id="saveGravityBtn">保存する</button>
    </div>
</div>