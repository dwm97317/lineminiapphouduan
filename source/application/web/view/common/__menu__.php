<?php foreach ($menu as $v): ?>
<li class="nav-item dropdown <?= $ctr==$v['parent']?'open':''; ?>">
                    <?php if (isset($v['child']) && !empty($v['child'])): ?>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                    <?php else: ?>
                    <a class="dropdown-toggle"  href="<?= $v['url']; ?>">
                    <?php endif; ?>
                    <span class="icon-holder">
                        <i class="mdi <?= $v['icon']; ?>"></i>
                    </span>
                    <span class="title <?= $ctr_path==$v['url_']?'current':''; ?>"><?= $v['name'] ?></span>
                    <?php if (isset($v['child']) && !empty($v['child'])): ?>
                    <span class="arrow">
                        <i class="mdi mdi-chevron-right"></i>
                    </span>
                    <?php endif; ?>
                </a>
                <?php if (isset($v['child']) && !empty($v['child'])): ?>
                <ul class="dropdown-menu">
                    <?php foreach ($v['child'] as $c): ?>
                    <li>
                        <a class="<?= $ctr_path==$c['url_']?'current':''; ?>" href="<?= $c['url']; ?>"><?= $c['name'] ?></a>
                    </li>
                    <?php endforeach; ?>  
                   
                </ul>
                <?php endif; ?>
            </li>
<?php endforeach; ?>                        