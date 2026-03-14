-- Расширение схемы для страницы детальной информации о проекте
-- Выполнить: mysql -u root -p yii1_db < schema/project.sql
-- Если колонки уже существуют, пропустить соответствующие строки

ALTER TABLE `tbl_partnership`
  ADD COLUMN `subtasks` TEXT NULL COMMENT 'JSON: массив подзадач' AFTER `interaction_format`;
ALTER TABLE `tbl_partnership`
  ADD COLUMN `goals` TEXT NULL COMMENT 'JSON: массив целей' AFTER `subtasks`;
ALTER TABLE `tbl_partnership`
  ADD COLUMN `events` TEXT NULL COMMENT 'JSON: массив событий {date, title, location}' AFTER `goals`;
