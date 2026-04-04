<?php
$pdo = new PDO('mysql:host=mysql;dbname=yii1_db', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$commands = [
    "ALTER TABLE tbl_partnership ADD COLUMN org_name_en VARCHAR(255) DEFAULT '' AFTER org_name",
    "ALTER TABLE tbl_partnership ADD COLUMN description_en TEXT NULL AFTER description",
    "ALTER TABLE tbl_partnership ADD COLUMN subtasks_en TEXT NULL AFTER subtasks",
    "ALTER TABLE tbl_partnership ADD COLUMN goals_en TEXT NULL AFTER goals",
    "ALTER TABLE tbl_partnership ADD COLUMN description_images TEXT NULL AFTER file_path",
];

foreach ($commands as $sql) {
    try {
        $pdo->exec($sql);
        echo "Successfully executed: $sql<br>";
    } catch (Exception $e) {
        echo "Error or already exists ($sql): " . $e->getMessage() . "<br>";
    }
}
echo "Migration complete!";
