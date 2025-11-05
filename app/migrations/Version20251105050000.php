<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

final class Version20251105050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed: 9 Trucks, 9 Trailers (001–003 free), 6 FleetSets (004–009), Drivers per set (0 or 2), and 24 ServiceOrders (Open/InProgress/Done).';
    }

    /** Ensure schema (tables) migration runs first */
    public function getDependencies(): array
    {
        return [\DoctrineMigrations\Version20251105024436::class];
    }

    /** Convert DateTimeImmutable → DB string using current platform format */
    private function ts(\DateTimeImmutable $dt): string
    {
        $fmt = $this->connection->getDatabasePlatform()->getDateTimeFormatString();

        return $dt->format($fmt);
    }

    /** Return 16-byte binary for ULID (column type BINARY(16)) */
    private function ulidBin(Ulid|string $ulid): string
    {
        $u = $ulid instanceof Ulid ? $ulid : new Ulid($ulid);

        return $u->toBinary();
    }

    public function up(Schema $schema): void
    {
        $now = new \DateTimeImmutable();

        // ---------- Trucks (9) ----------
        $truckPlates = ['TRK-001','TRK-002','TRK-003','TRK-004','TRK-005','TRK-006','TRK-007','TRK-008','TRK-009'];
        $truckModels = ['FH16','R450','XF','Actros','T-Series','TGX','Iveco S-Way','Actros','FH16'];
        $truckBrands = ['Volvo','Scania','DAF','Mercedes','Renault','MAN','Iveco','Mercedes','Volvo'];
        $truckInService = [0,1,0,1,1,1,1,0,0];
        $truckIds    = array_map(fn() => (string) new Ulid(), range(1, 9));

        foreach (range(0, 8) as $i) {
            $this->addSql(
                'INSERT INTO truck (id, model, brand, plate, in_service, created_at, updated_at)
                 VALUES (:id, :model, :brand, :plate, :in_service, :created, :updated)',
                [
                    'id' => $this->ulidBin($truckIds[$i]),
                    'model' => $truckModels[$i],
                    'brand' => $truckBrands[$i],
                    'plate' => $truckPlates[$i],
                    'in_service' => $truckInService[$i],
                    'created' => $this->ts($now),
                    'updated' => $this->ts($now),
                ],
                [ParameterType::BINARY, 'string', 'string', 'string', 'integer', 'string', 'string']
            );
        }

        // ---------- Trailers (9) ----------
        $trailerPlates = ['TRL-001','TRL-002','TRL-003','TRL-004','TRL-005','TRL-006','TRL-007','TRL-008','TRL-009'];
        $trailerInService = [0,1,0,1,1,1,1,0,0];
        $trailerIds    = array_map(fn() => (string) new Ulid(), range(1, 9));

        foreach (range(0, 8) as $i) {
            $this->addSql(
                'INSERT INTO trailer (id, plate, in_service, created_at, updated_at)
                 VALUES (:id, :plate, :in_service, :created, :updated)',
                [
                    'id' => $this->ulidBin($trailerIds[$i]),
                    'plate' => $trailerPlates[$i],
                    'in_service' => $trailerInService[$i],
                    'created' => $this->ts($now),
                    'updated' => $this->ts($now),
                ],
                [ParameterType::BINARY, 'string', 'integer', 'string', 'string']
            );
        }

        // ---------- FleetSets (6) ----------
        // Keep 001..003 free; pair 004..009 (indices 3..8)
        $fleetSetIds = array_map(fn() => (string) new Ulid(), range(1, 6));
        $pairIndices = [3,4,5,6,7,8]; // TRK/TRL 004..009

        foreach (range(0, 5) as $k) {
            $ti = $pairIndices[$k];
            $this->addSql(
                'INSERT INTO fleet_set (id, truck_id, trailer_id, created_at, updated_at)
                 VALUES (:id, :truck, :trailer, :created, :updated)',
                [
                    'id'      => $this->ulidBin($fleetSetIds[$k]),
                    'truck'   => $this->ulidBin($truckIds[$ti]),
                    'trailer' => $this->ulidBin($trailerIds[$ti]),
                    'created' => $this->ts($now),
                    'updated' => $this->ts($now),
                ],
                [ParameterType::BINARY, ParameterType::BINARY, ParameterType::BINARY, 'string', 'string']
            );
        }

        // ---------- Drivers ----------
        // For each status group, two sets: first w/o drivers, second with 2 drivers.
        // Open:        fleetSetIds[0] (none), fleetSetIds[1] (2 drivers)
        // InProgress:  fleetSetIds[2] (none), fleetSetIds[3] (2 drivers)
        // Done:        fleetSetIds[4] (none), fleetSetIds[5] (2 drivers)
        $driverSeeds = [
            'Driver A1','Driver A2', // for fleetSetIds[1]
            'Driver B1','Driver B2', // for fleetSetIds[3]
            'Driver C1','Driver C2', // for fleetSetIds[5]
        ];
        $driverIds = [];
        foreach ($driverSeeds as $name) {
            $id = (string) new Ulid();
            $driverIds[] = $id;
            $this->addSql(
                'INSERT INTO driver (id, name, created_at, updated_at)
                 VALUES (:id, :name, :created, :updated)',
                [
                    'id'      => $this->ulidBin($id),
                    'name'    => $name,
                    'created' => $this->ts($now),
                    'updated' => $this->ts($now),
                ],
                [ParameterType::BINARY, 'string', 'string', 'string']
            );
        }

        // driver_fleet_set assignments
        $assignments = [
            [$driverIds[0], $fleetSetIds[1]],
            [$driverIds[1], $fleetSetIds[1]],
            [$driverIds[2], $fleetSetIds[3]],
            [$driverIds[3], $fleetSetIds[3]],
            [$driverIds[4], $fleetSetIds[5]],
            [$driverIds[5], $fleetSetIds[5]],
        ];
        foreach ($assignments as [$driverId, $setId]) {
            $this->addSql(
                'INSERT INTO driver_fleet_set (driver_id, fleet_set_id) VALUES (:d, :s)',
                ['d' => $this->ulidBin($driverId), 's' => $this->ulidBin($setId)],
                [ParameterType::BINARY, ParameterType::BINARY]
            );
        }

        // ---------- Service Orders (24 total) ----------
        $statuses = ['Open', 'InProgress', 'Done'];
        $mkCreated = fn(int $n) => $now->modify(sprintf('-%d days', $n % 7));
        $mkUpdated = fn(\DateTimeImmutable $c) => $c->modify('+1 hour');

        $orderIdx = 0;
        foreach ($statuses as $groupIdx => $status) {
            // Trucks: free (001–003)
            foreach ([0,1,2] as $i) {
                $orderIdx++;
                $created = $mkCreated($orderIdx);
                $this->addSql(
                    'INSERT INTO service_order (id, status, subject_type, subject_id, created_at, updated_at)
                     VALUES (:id, :status, :stype, :sid, :created, :updated)',
                    [
                        'id'      => $this->ulidBin(new Ulid()),
                        'status'  => $status,
                        'stype'   => 'Truck',
                        'sid'     => $this->ulidBin($truckIds[$i]),
                        'created' => $this->ts($created),
                        'updated' => $this->ts($mkUpdated($created)),
                    ],
                    [ParameterType::BINARY, 'string', 'string', ParameterType::BINARY, 'string', 'string']
                );
            }

            // Trailers: free (001–003)
            foreach ([0,1,2] as $i) {
                $orderIdx++;
                $created = $mkCreated($orderIdx);
                $this->addSql(
                    'INSERT INTO service_order (id, status, subject_type, subject_id, created_at, updated_at)
                     VALUES (:id, :status, :stype, :sid, :created, :updated)',
                    [
                        'id'      => $this->ulidBin(new Ulid()),
                        'status'  => $status,
                        'stype'   => 'Trailer',
                        'sid'     => $this->ulidBin($trailerIds[$i]),
                        'created' => $this->ts($created),
                        'updated' => $this->ts($mkUpdated($created)),
                    ],
                    [ParameterType::BINARY, 'string', 'string', ParameterType::BINARY, 'string', 'string']
                );
            }

            // FleetSets: two per status (idx pair)
            $fsA = $fleetSetIds[$groupIdx*2 + 0]; // no drivers
            $fsB = $fleetSetIds[$groupIdx*2 + 1]; // with 2 drivers
            foreach ([$fsA, $fsB] as $fsId) {
                $orderIdx++;
                $created = $mkCreated($orderIdx);
                $this->addSql(
                    'INSERT INTO service_order (id, status, subject_type, subject_id, created_at, updated_at)
                     VALUES (:id, :status, :stype, :sid, :created, :updated)',
                    [
                        'id'      => $this->ulidBin(new Ulid()),
                        'status'  => $status,
                        'stype'   => 'FleetSet',
                        'sid'     => $this->ulidBin($fsId),
                        'created' => $this->ts($created),
                        'updated' => $this->ts($mkUpdated($created)),
                    ],
                    [ParameterType::BINARY, 'string', 'string', ParameterType::BINARY, 'string', 'string']
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Remove service orders (Truck subjects: free TRK-001..003)
        $this->addSql(
            'DELETE so FROM service_order so
             JOIN truck t ON t.id = so.subject_id
             WHERE so.subject_type = :stype AND t.plate IN (:p1, :p2, :p3)',
            ['stype' => 'Truck', 'p1' => 'TRK-001', 'p2' => 'TRK-002', 'p3' => 'TRK-003'],
            ['string','string','string','string']
        );

        // Remove service orders (Trailer subjects: free TRL-001..003)
        $this->addSql(
            'DELETE so FROM service_order so
             JOIN trailer tr ON tr.id = so.subject_id
             WHERE so.subject_type = :stype AND tr.plate IN (:p1, :p2, :p3)',
            ['stype' => 'Trailer', 'p1' => 'TRL-001', 'p2' => 'TRL-002', 'p3' => 'TRL-003'],
            ['string','string','string','string']
        );

        // Remove service orders (FleetSet subjects: pairings 004..009)
        $this->addSql(
            'DELETE so FROM service_order so
             WHERE so.subject_type = :stype
               AND so.subject_id IN (
                 SELECT fs.id FROM fleet_set fs
                 JOIN truck t ON t.id = fs.truck_id
                 JOIN trailer tr ON tr.id = fs.trailer_id
                 WHERE t.plate IN (:tp1,:tp2,:tp3,:tp4,:tp5,:tp6)
                   AND tr.plate IN (:rp1,:rp2,:rp3,:rp4,:rp5,:rp6)
               )',
            [
                'stype' => 'FleetSet',
                'tp1'=>'TRK-004','tp2'=>'TRK-005','tp3'=>'TRK-006','tp4'=>'TRK-007','tp5'=>'TRK-008','tp6'=>'TRK-009',
                'rp1'=>'TRL-004','rp2'=>'TRL-005','rp3'=>'TRL-006','rp4'=>'TRL-007','rp5'=>'TRL-008','rp6'=>'TRL-009',
            ],
            ['string','string','string','string','string','string','string','string','string','string','string','string','string']
        );

        // Remove driver links for those fleet sets
        $this->addSql(
            'DELETE dfs FROM driver_fleet_set dfs
             WHERE dfs.fleet_set_id IN (
                 SELECT fs.id FROM fleet_set fs
                 JOIN truck t ON t.id = fs.truck_id
                 JOIN trailer tr ON tr.id = fs.trailer_id
                 WHERE t.plate IN (:tp1,:tp2,:tp3,:tp4,:tp5,:tp6)
                   AND tr.plate IN (:rp1,:rp2,:rp3,:rp4,:rp5,:rp6)
             )',
            [
                'tp1'=>'TRK-004','tp2'=>'TRK-005','tp3'=>'TRK-006','tp4'=>'TRK-007','tp5'=>'TRK-008','tp6'=>'TRK-009',
                'rp1'=>'TRL-004','rp2'=>'TRL-005','rp3'=>'TRL-006','rp4'=>'TRL-007','rp5'=>'TRL-008','rp6'=>'TRL-009',
            ],
            ['string','string','string','string','string','string','string','string','string','string','string','string']
        );

        // Remove the drivers we created (by names)
        $this->addSql(
            'DELETE FROM driver WHERE name IN (:n1,:n2,:n3,:n4,:n5,:n6)',
            ['n1'=>'Driver A1','n2'=>'Driver A2','n3'=>'Driver B1','n4'=>'Driver B2','n5'=>'Driver C1','n6'=>'Driver C2'],
            ['string','string','string','string','string','string']
        );

        // Remove the 6 FleetSets (004..009 pairings)
        $this->addSql(
            'DELETE fs FROM fleet_set fs
             JOIN truck t ON t.id = fs.truck_id
             JOIN trailer tr ON tr.id = fs.trailer_id
             WHERE t.plate IN (:tp1,:tp2,:tp3,:tp4,:tp5,:tp6)
               AND tr.plate IN (:rp1,:rp2,:rp3,:rp4,:rp5,:rp6)',
            [
                'tp1'=>'TRK-004','tp2'=>'TRK-005','tp3'=>'TRK-006','tp4'=>'TRK-007','tp5'=>'TRK-008','tp6'=>'TRK-009',
                'rp1'=>'TRL-004','rp2'=>'TRL-005','rp3'=>'TRL-006','rp4'=>'TRL-007','rp5'=>'TRL-008','rp6'=>'TRL-009',
            ],
            ['string','string','string','string','string','string','string','string','string','string','string','string']
        );

        // Remove all 9 trailers
        $this->addSql(
            'DELETE FROM trailer WHERE plate IN (:p1,:p2,:p3,:p4,:p5,:p6,:p7,:p8,:p9)',
            ['p1'=>'TRL-001','p2'=>'TRL-002','p3'=>'TRL-003','p4'=>'TRL-004','p5'=>'TRL-005','p6'=>'TRL-006','p7'=>'TRL-007','p8'=>'TRL-008','p9'=>'TRL-009'],
            ['string','string','string','string','string','string','string','string','string']
        );

        // Remove all 9 trucks
        $this->addSql(
            'DELETE FROM truck WHERE plate IN (:p1,:p2,:p3,:p4,:p5,:p6,:p7,:p8,:p9)',
            ['p1'=>'TRK-001','p2'=>'TRK-002','p3'=>'TRK-003','p4'=>'TRK-004','p5'=>'TRK-005','p6'=>'TRK-006','p7'=>'TRK-007','p8'=>'TRK-008','p9'=>'TRK-009'],
            ['string','string','string','string','string','string','string','string','string']
        );
    }
}
