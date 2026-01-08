<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class userProfileModel extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $table = 'usersprofile';
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'NAME',
        'ACC_MODE',
        'PIN',
        'DOOR_GRP',
        'BEGIN_DATE',
        'END_DATE',
        'BIRTHDAY',
        'Depid',
        'MemberNo',
        'Branchid',
        'photo',
        'Card',
        'NoIdentitas',
        'timezone',
        'shiftpatternID',
        'PASSWORD',
        'user_type'
    ];

    protected $hidden = [
        'PASSWORD',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }

    public function shiftPattern()
    {
        return $this->belongsTo(ShiftPattern::class, 'PatternID', 'Id');
    }

    // Other relationships remain the same
    public function department()
    {
        return $this->belongsTo(departmentModel::class, 'Depid', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'Branchid', 'id');
    }

    public function deviceGate()
    {
        return $this->belongsTo(deviceGateModel::class, 'ID', 'id');
    }

    public function deviceGroup()
    {
        return $this->belongsTo(DeviceGroupModel::class, 'ID', 'id');
    }

    public function userData()
    {
        return $this->hasMany(userDataModel::class, 'fid', 'ID');
    }

    public function userLog()
    {
        return $this->hasMany(userLogModel::class, 'USER_ADDR', 'ID');
    }

    public function leaveProcesses()
    {
        return $this->hasMany(LeaveProcess::class, 'EmplID', 'ID');
    }

    public function attendSummaries()
    {
        return $this->hasMany(AttendSumary::class, 'EmployeeID', 'ID');
    }

    public function weekzoneUsers()
    {
        return $this->hasMany(WeekzoneUser::class, 'userid', 'ID');
    }

    public function usersDevices()
    {
        return $this->hasMany(usersDevice::class, 'userId', 'ID');
    }
}