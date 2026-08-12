/*
* gravity_picker_ui.php
* プロッター部分だけを独立させました。他の画面でも使い回せます。
*
*/
<div class="gravity-picker-container">
    <div class="gravity-box" id="gravity-box">
        <div class="guide-line-v"></div>
        <div class="guide-line-h"></div>
        <div class="gravity-marker" id="gravity-marker">○</div>
        <div class="click-grid" id="click-grid">
            <?php for($i=0; $i<9; $i++): ?>
                <div class="grid-cell" data-index="<?= $i ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
    <div class="depth-selector">
        <button type="button" class="btn-depth" data-depth="front">表</button>
        <button type="button" class="btn-depth" data-depth="center">中央</button>
        <button type="button" class="btn-depth" data-depth="back">裏</button>
    </div>
</div>