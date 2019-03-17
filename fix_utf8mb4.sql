# Commented queries are fixed, other one are normally not concerned.

#UPDATE `asset`
#SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(name) != LENGTH(name);
#UPDATE `asset`
#SET storage_id = CONVERT(BINARY CONVERT(storage_id USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(storage_id) != LENGTH(storage_id);
#UPDATE `asset`
#SET extension = CONVERT(BINARY CONVERT(extension USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(extension) != LENGTH(extension);

UPDATE `job`
SET log = CONVERT(BINARY CONVERT(log USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(log) != LENGTH(log);

#UPDATE `media`
#SET data = CONVERT(BINARY CONVERT(data USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(data) != LENGTH(data);
#UPDATE `media`
#SET source = CONVERT(BINARY CONVERT(source USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(source) != LENGTH(source);
## Allows to manage module ArchiveRepertory.
#UPDATE `media`
#SET storage_id = CONVERT(BINARY CONVERT(storage_id USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(storage_id) != LENGTH(storage_id);
#UPDATE `media`
#SET extension = CONVERT(BINARY CONVERT(extension USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(extension) != LENGTH(extension);

UPDATE `property`
SET label = CONVERT(BINARY CONVERT(label USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(label) != LENGTH(label);
UPDATE `property`
SET comment = CONVERT(BINARY CONVERT(comment USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(comment) != LENGTH(comment);

UPDATE `resource_class`
SET label = CONVERT(BINARY CONVERT(label USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(label) != LENGTH(label);
UPDATE `resource_class`
SET comment = CONVERT(BINARY CONVERT(comment USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(comment) != LENGTH(comment);

UPDATE `resource_template`
SET label = CONVERT(BINARY CONVERT(label USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(label) != LENGTH(label);

UPDATE `resource_template_property`
SET alternate_label = CONVERT(BINARY CONVERT(alternate_label USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(alternate_label) != LENGTH(alternate_label);
UPDATE `resource_template_property`
SET alternate_comment = CONVERT(BINARY CONVERT(alternate_comment USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(alternate_comment) != LENGTH(alternate_comment);

#UPDATE `setting`
#SET value = CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(value) != LENGTH(value);

#UPDATE `site`
#SET title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(title) != LENGTH(title);
#UPDATE `site`
#SET summary = CONVERT(BINARY CONVERT(summary USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(summary) != LENGTH(summary);
#UPDATE `site`
#SET navigation = CONVERT(BINARY CONVERT(navigation USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(navigation) != LENGTH(navigation);
#UPDATE `site`
#SET item_pool = CONVERT(BINARY CONVERT(item_pool USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(item_pool) != LENGTH(item_pool);

#UPDATE `site_block_attachment`
#SET caption = CONVERT(BINARY CONVERT(caption USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(caption) != LENGTH(caption);

#UPDATE `site_page`
#SET title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(title) != LENGTH(title);

#UPDATE `site_page_block`
#SET data = CONVERT(BINARY CONVERT(data USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(data) != LENGTH(data);

#UPDATE `site_setting`
#SET value = CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(value) != LENGTH(value);

# Generally used in many digital libraries.
#UPDATE `tag`
#SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(name) != LENGTH(name);

#UPDATE `user`
#SET email = CONVERT(BINARY CONVERT(email USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(email) != LENGTH(email);
#UPDATE `user`
#SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(name) != LENGTH(name);

#UPDATE `user_setting`
#SET value = CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(value) != LENGTH(value);

#UPDATE `value`
#SET value = CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4)
#WHERE CHAR_LENGTH(value) != LENGTH(value);

UPDATE `vocabulary`
SET label = CONVERT(BINARY CONVERT(label USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(label) != LENGTH(label);
UPDATE `vocabulary`
SET comment = CONVERT(BINARY CONVERT(comment USING latin1) USING utf8mb4)
WHERE CHAR_LENGTH(comment) != LENGTH(comment);
