<?php declare(strict_types=1);

namespace NZTim\MailLog\Persistence;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use NZTim\MailLog\Entry;
use stdClass;

class EntryRepo
{
    protected EntryHydrator $hydrator;
    protected Connection $db;
    private string $table = 'mail_log';

    public function __construct(EntryHydrator $hydrator, DatabaseManager $dbmgr)
    {
        $this->hydrator = $hydrator;
        $this->db = $dbmgr->connection(config('database.maillog'));
    }

    // Read -------------------------------------------------------------------

    public function findById(int $id): ?Entry
    {
        $row = $this->db->table($this->table)->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    public function findOld(): Collection
    {
        $rows = $this->db->table($this->table)
            ->where('date', '<', now()->subMonth())
            ->get();
        return $this->hydrateCollection($rows);
    }

    public function findByMessageId(string $messageId): ?Entry
    {
        $row = $this->db->table($this->table)->where('message_id', $messageId)->first();
        return $row ? $this->hydrate($row) : null;
    }

    public function index(string $search, string $status): LengthAwarePaginator
    {
        $rows = $this->db->table($this->table);
        if ($search) {
            $rows = $rows->where('recipient', 'LIKE', "%{$search}%");
        }
        if ($status) {
            $rows = $rows->where('status', $status);
        }
        $rows = $rows->orderBy('date', 'desc')->paginate(25);
        return $this->hydrateLengthAwarePaginator($rows);
    }

    public function all(): Collection
    {
        $rows = $this->db->table($this->table)->orderBy('id')->get();
        return $this->hydrateCollection($rows);
    }

    // Hydrate ----------------------------------------------------------------

    private function hydrate(stdClass $data): Entry
    {
        return $this->hydrator->hydrate((array)$data);
    }

    /** @return Entry[]|Collection */
    private function hydrateCollection(Collection $collection): Collection
    {
        return $collection->map(function (stdClass $data) {
            return $this->hydrate($data);
        });
    }

    /** @return LengthAwarePaginator|Entry[] */
    private function hydrateLengthAwarePaginator(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->setCollection($this->hydrateCollection($paginator->getCollection()));
    }

    // Write ------------------------------------------------------------------

    public function persist(Entry $model): int
    {
        $data = $this->hydrator->extract($model);
        if (is_null($model->id)) {
            return $this->db->table($this->table)->insertGetId($data);
        }
        $this->db->table($this->table)->where('id', $model->id)->update($data);
        return $model->id;
    }

    public function setDelivered(Entry $entry): Entry
    {
        if ($entry->isDelivered()) {
            return $entry;
        }
        $entry->status = Entry::STATUS_DELIVERED;
        $entry->updated = now();
        $this->persist($entry);
        return $this->findById($entry->id);
    }

    public function setBounced(Entry $entry, array $data): Entry
    {
        if ($entry->isBounce()) {
            return $entry;
        }
        $entry->status = Entry::STATUS_BOUNCE;
        $entry->data[Entry::DATA_KEY_SES_BOUNCE] = $data;
        $entry->updated = now();
        $this->persist($entry);
        return $this->findById($entry->id);
    }

    public function setComplaint(Entry $entry, array $data): Entry
    {
        if ($entry->isComplaint()) {
            return $entry;
        }
        $entry->status = Entry::STATUS_COMPLAINT;
        $entry->data[Entry::DATA_KEY_SES_COMPLAINT] = $data;
        $entry->updated = now();
        $this->persist($entry);
        return $this->findById($entry->id);
    }

    public function delete(Entry $model): void
    {
        $this->db->table($this->table)->where('id', $model->id)->delete();
    }
}
