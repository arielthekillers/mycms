<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TRIGGER trg_media_usage_increment
            AFTER INSERT ON media_usages
            FOR EACH ROW
            BEGIN
                UPDATE media SET usage_count = usage_count + 1 WHERE id = NEW.media_id;
            END
        SQL);

        $db->execute(<<<SQL
            CREATE TRIGGER trg_media_usage_decrement
            AFTER DELETE ON media_usages
            FOR EACH ROW
            BEGIN
                UPDATE media SET usage_count = GREATEST(usage_count - 1, 0) WHERE id = OLD.media_id;
            END
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TRIGGER IF EXISTS trg_media_usage_decrement');
        $db->execute('DROP TRIGGER IF EXISTS trg_media_usage_increment');
    }
};
