<?php
namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class WireAdminSetupDatabase extends Component
{
    public $forms=[];
    public $message=[];

    public function mount()
    {

    }

    public function render()
    {


        // Wrap the database connection attempt in a try-catch block
        try {
            // Attempt to establish a database connection
            $pdo = DB::connection()->getPdo();

            // If the connection is successful, PDO instance will be available
            if ($pdo !== null) {
                // echo "Database connection is currently established.";
            } else {
                // echo "Database connection is not established.";
            }
        } catch (\PDOException $e) {
            // If an exception is thrown, it means the connection failed
            // echo "Database connection is not established. Error: " . $e->getMessage();
            $pdo = null;
        }

        return view("jiny-admin::setup.database",[
            'pdo' => $pdo
        ]);
    }

    public function submit()
    {
        $this->message = [];
        $this->message []= "root 데이터베이스 연결";

        //dd($this->message);

        // Database connection parameters
        $host = 'localhost';
        $dbname = 'mysql';
        $username = 'root';
        $password = '123456';

        try {
            // Connect to the database using PDO
            $pdo = new \PDO("mysql:host=$host;dbname=$dbname", $username, $password);

            // Set PDO attributes to throw exceptions for errors
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->message []= "root 데이터베이스 연결";

//             CREATE USER 'jiny'@'localhost' IDENTIFIED BY '123456';
// GRANT Create role ON *.* TO 'jiny'@'localhost';
// GRANT Create user ON *.* TO 'jiny'@'localhost';
// GRANT Drop role ON *.* TO 'jiny'@'localhost';
// GRANT Event ON *.* TO 'jiny'@'localhost';
// GRANT File ON *.* TO 'jiny'@'localhost';
// GRANT Process ON *.* TO 'jiny'@'localhost';
// GRANT Reload ON *.* TO 'jiny'@'localhost';
// GRANT Replication client ON *.* TO 'jiny'@'localhost';
// GRANT Replication slave ON *.* TO 'jiny'@'localhost';
// GRANT Show databases ON *.* TO 'jiny'@'localhost';
// GRANT Shutdown ON *.* TO 'jiny'@'localhost';
// GRANT Super ON *.* TO 'jiny'@'localhost';
// GRANT Create tablespace ON *.* TO 'jiny'@'localhost';
// GRANT Usage ON *.* TO 'jiny'@'localhost';
// GRANT INNODB_REDO_LOG_ARCHIVE ON *.* TO 'jiny'@'localhost';
// GRANT ENCRYPTION_KEY_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT CONNECTION_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT TABLE_ENCRYPTION_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT ROLE_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT REPLICATION_SLAVE_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SYSTEM_VARIABLES_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT BINLOG_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT AUDIT_ABORT_EXEMPT ON *.* TO 'jiny'@'localhost';
// GRANT SERVICE_CONNECTION_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SET_USER_ID ON *.* TO 'jiny'@'localhost';
// GRANT GROUP_REPLICATION_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT PASSWORDLESS_USER_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT XA_RECOVER_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT PERSIST_RO_VARIABLES_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SHOW_ROUTINE ON *.* TO 'jiny'@'localhost';
// GRANT BACKUP_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT GROUP_REPLICATION_STREAM ON *.* TO 'jiny'@'localhost';
// GRANT CLONE_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT RESOURCE_GROUP_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT FIREWALL_EXEMPT ON *.* TO 'jiny'@'localhost';
// GRANT RESOURCE_GROUP_USER ON *.* TO 'jiny'@'localhost';
// GRANT BINLOG_ENCRYPTION_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT APPLICATION_PASSWORD_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SYSTEM_USER ON *.* TO 'jiny'@'localhost';
// GRANT FLUSH_OPTIMIZER_COSTS ON *.* TO 'jiny'@'localhost';
// GRANT AUDIT_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT TELEMETRY_LOG_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT INNODB_REDO_LOG_ENABLE ON *.* TO 'jiny'@'localhost';
// GRANT REPLICATION_APPLIER ON *.* TO 'jiny'@'localhost';
// GRANT FLUSH_STATUS ON *.* TO 'jiny'@'localhost';
// GRANT FLUSH_USER_RESOURCES ON *.* TO 'jiny'@'localhost';
// GRANT FLUSH_TABLES ON *.* TO 'jiny'@'localhost';
// GRANT AUTHENTICATION_POLICY_ADMIN ON *.* TO 'jiny'@'localhost';
// GRANT SENSITIVE_VARIABLES_OBSERVER ON *.* TO 'jiny'@'localhost';
// GRANT SET_ANY_DEFINER ON *.* TO 'jiny'@'localhost';
// GRANT ALLOW_NONEXISTENT_DEFINER ON *.* TO 'jiny'@'localhost';
// GRANT TRANSACTION_GTID_TAG ON *.* TO 'jiny'@'localhost';
// GRANT Grant option ON *.* TO 'jiny'@'localhost';

            /*
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");

            // Bind parameters
            $name = 'John Doe';
            $email = 'john@example.com';
            $password = password_hash('password', PASSWORD_DEFAULT); // Hash the password
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);

            // Execute the statement
            $stmt->execute();
            */

            echo "New user added successfully.";
        } catch(PDOException $e) {
            // If an exception is caught, it means the operation failed
            echo "Error: " . $e->getMessage();
        }

        //dd($this->message);







    }

}
