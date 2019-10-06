<?php

namespace App\Http\Controllers\Admin;

use App\Models\ClassRoom;
use App\Models\FeeReceipt;
use App\Models\FeeType;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\FeeReceiptRequest as StoreRequest;
use App\Http\Requests\FeeReceiptRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use App\User;
use http\Env\Request;

/**
 * Class FeeReceiptCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class FeeReceiptCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\FeeReceipt');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/fee-receipt');
        $this->crud->setEntityNameStrings('Fee Receipt', 'fee receipts');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        $userAdminStudents = User::getAdminStudents();
        $userAdminStudentsWithAllStudents [] = 'All Students';
        foreach ($userAdminStudents as $userAdminStudent) {
            $userAdminStudentsWithAllStudents [] = $userAdminStudent;
        }
//        $userAdminStudentsWithAllStudents [] = User::getAdminStudents();
//        dd($userAdminStudentsWithAllStudents);

        // TODO: remove setFromDb() and manually define Fields and Columns
//        $this->crud->setFromDb();
        $this->crud->addColumns([
            [
                'name' => 'row_number',
                'type' => 'row_number',
                'label' => 'Sr. #',
                'orderable' => false,
            ],
            [
                'label' => 'Student Name',
                'name' => 'student_id',
                'type' => 'select',
                'entity' => 'student',
                'attribute' => 'name'
            ],
            [
                'label' => 'Fee Type',
                'name' => 'fee_type_id',
                'type' => 'select',
                'entity' => 'feeType',
                'attribute' => 'type'
            ],
            [
                'label' => 'Amount',
                'name' => 'amount',
            ],
            [
                'label' => 'Amount',
                'name' => 'amount',
            ],

            [
                'label' => 'Due Date',
                'name' => 'due_date',
                'type' => 'date'
            ],
            [
                'label' => 'Status',
                'name' => 'status',
            ],
            [
                'label' => 'Submitted Amount',
                'name' => 'submitted_amount',
            ],
            [
                'label' => 'Submission Date',
                'name' => 'submission_date',
            ],
        ]);
        $this->crud->addFields([
            [
                'label' => 'Fee Type',
                'name' => 'fee_type_id',
                'type' => 'select',
                'entity' => 'feeType',
                'attribute' => 'type'
            ],
            [
                'name' => 'student_id',
                'label' => "Student Name",
                'type' => 'select_from_array',
                'options' => $userAdminStudents,
                'allows_null' => false,
            ],
            [
                'label' => 'Amount',
                'name' => 'amount',
            ],
            [
                'label' => 'Amount',
                'name' => 'amount',
            ],

            [
                'label' => 'Due Date',
                'name' => 'due_date',
                'type' => 'date'
            ],
            [
                'label' => 'Status',
                'name' => 'status',
                'type' => 'enum'
            ],
            [
                'label' => 'Submitted Amount',
                'name' => 'submitted_amount',
            ],
            [
                'label' => 'Submission Date',
                'name' => 'submission_date',
                'type' => 'date_picker',
                'allows_null' => true,
                'default' => true
            ],
        ]);
        // add asterisk for fields that are required in FeeReceiptRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');

        $this->crud->addClause('whereHas', 'student', function ($query) {
            $query->where('admin_id', '=', backpack_user()->id);
        });
    }

    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function generateReceiptForm()
    {

        $classRooms = ClassRoom::where('admin_id', backpack_user()->id)->get();
        $feeTypes = FeeType::where('admin_id', backpack_user()->id)->get();
        return view('vendor/backpack/base/generateReceipts', compact('classRooms', 'feeTypes'));
    }

    public function generateReceipt()
    {
        request()->validate([
            'fee_type' => 'required',
            'class' => 'required',
            'due_date' => 'required|date',
        ]);
        $class = ClassRoom::find(request()->class);

        $class_fee = $class->classFee->where('fee_type_id', request()->fee_type)->first();

        $student_count = 0;
        foreach ($class->students->where('admin_id')->pluck('student_id') as $student_id)
            FeeReceipt::create([
                'student_id' => $student_id,
                'fee_type_id' => request()->fee_type,
                'amount' => $class_fee->amount,
                'submitted_amount' => 0,
                'due_date' => request()->due_date,
                'status' => 'Pending',
            ]);

        if ($student_count > 0) {
            $message = 'All receipts are generated successfully.';
        } else {
            $message = 'No receipts were generated because this class has no students';
        }
        $classRooms = ClassRoom::where('admin_id', backpack_user()->id)->get();
        $feeTypes = FeeType::where('admin_id', backpack_user()->id)->get();
        return redirect('admin/fee-receipt/generate')->with([
            'message' => $message,
            'classRooms' => $classRooms,
            'feeTypes' => $feeTypes,
            'student_count' => $student_count
        ]);
    }

//    public function classFeeCheck($class_fee){
//        if($class_fee !== null){
//            return null;
//        }
//        else{
//            $classRooms = ClassRoom::where('admin_id',backpack_user()->id)->get();
//            $feeTypes = FeeType::where('admin_id',backpack_user()->id)->get();
//            $message = 'Please create fee for this class first';
//
//            return redirect('admin/fee-receipt/generate')->with([
//                'message' => $message,
//                'classRooms' => $classRooms,
//                'feeTypes' => $feeTypes,
//            ]);
//        }
//    }
}
