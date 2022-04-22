<?php
    $cpage = isset($_GET['cpage']) ? abs((int)$_GET['cpage']) : 1;
    $page = isset($_GET['page']) ? $_GET['page'] : PPWP_SEC_MENU_SLUG;
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'ppwp_sec_block';
    $postPerPage = 2;
    $data = PPWP_SEC_DB::getDataPagination(PPWP_SEC_TABLE_LOCK, $postPerPage, $cpage);

    if (!empty($_POST)) {
        $result = PPWP_SEC_DB::update(
            PPWP_SEC_TABLE_LOCK,
            [
                'attempt' => $_POST['attempt'],
                'blocked' => !empty($_POST['blocked']) ? 1 : 0
            ],
            ['id' => $_POST['id']]
        );
        wp_redirect(admin_url() . 'admin.php?page=' . $page . '&tab=' . $tab . '&cpage=' . $cpage);
    }
    if (!empty($_GET['action']) && $_GET['action'] === "edit" && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $lockIpById = PPWP_SEC_DB::get(PPWP_SEC_TABLE_LOCK, "id = $id", 'ARRAY_A');
    }
    if (!empty($_GET['action']) && $_GET['action'] === "delete" && !empty($_GET['id'])) {
        if(PPWP_SEC_DB::delete(PPWP_SEC_TABLE_LOCK, ["id" => $_GET['id']])) {
            wp_redirect(admin_url() . 'admin.php?page=' . $page . '&tab=' . $tab);
        }
    }
?>
<style>
    .pagination {
        display: inline-block;
        width: 100%;
    }

    .pagination span, .pagination a {
        float: left;
        padding: 8px 16px;
        text-decoration: none;
    }
</style>

<?php if(empty($_GET['action'])) { ?>
<table class="table">
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">IP</th>
            <th scope="col">Page ID</th>
            <th scope="col">Attempts</th>
            <th scope="col">Blocked</th>
            <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data['results'] as $row) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['ip'] ?></td>
            <td><?= $row['page_id'] ?></td>
            <td><?= $row['attempt'] ?></td>
            <td><?= $row['blocked'] == true ? "true" : "false" ?></td>

            <td><a href="?<?= http_build_query(['page' => $page, 'tab' => $tab, 'cpage' => $cpage, 'action' => 'edit', 'id' => $row['id']]) ?>">Edit</a> |
                <a href="?<?= http_build_query(['page' => $page, 'tab' => $tab, 'cpage' => $cpage, 'action' => 'delete', 'id' => $row['id']]) ?> ?>">Delete</a></td>
        </tr>
        <?php } ?>
    </tbody>
</table><?php
    echo '<div class="pagination">';
    echo paginate_links( array(
        'base' => add_query_arg( 'cpage', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => ceil($data['total'] / $postPerPage),
        'current' => $cpage,
        'type' => 'list'
    ));
    echo '</div>';
    ?>
<?php } ?>

<?php if(!empty($_GET['action']) && $_GET['action'] === "edit" && !empty($lockIpById)) { ?>
    <form action="" method="post">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">IP</th>
                <th scope="col">Page ID</th>
                <th scope="col">Attempts</th>
                <th scope="col">Blocked</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <input type="hidden" value="<?= $lockIpById['id'] ?>" name="id">
                <td><?= $lockIpById['id'] ?></td>
                <td><?= $lockIpById['ip'] ?></td>
                <td><?= $lockIpById['page_id'] ?></td>
                <td><input type="text" value="<?= $lockIpById['attempt'] ?>" name="attempt"></td>
                <td><input type="checkbox" name="blocked" value="1" <?= $lockIpById['blocked'] ? "checked" : "" ?>></td>
                <td><input type="submit" value="Update"></td>
            </tr>
            </tbody>
        </table>
    </form>
<?php } ?>




