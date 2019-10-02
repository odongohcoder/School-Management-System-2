<?php

namespace App\Http\Controllers\Admin;

use App\Models\Result;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\User;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ResultRequest as StoreRequest;
use App\Http\Requests\ResultRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class ResultCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ResultCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Result');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/result');
        $this->crud->setEntityNameStrings('result', 'results');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

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
                'name' => 'exam_session',
                'label' => "Exam Session"
            ],
            [
                'label' => 'Student Name',
                'name' => 'student_id',
                'type' => 'select',
                'entity' => 'student',
                'attribute' => 'name'
            ],
            [
                'label' => 'Subject',
                'name' => 'exam_id',
                'type' => 'select',
                'entity' => 'exam.subject',
                'attribute' => 'title'
            ],
            [
                'label' => 'Total Marks',
                'name' => 'total_marks',
            ],
            [
                'label' => 'Obtained Marks',
                'name' => 'obtained_marks',
            ],
            [
                'label' => 'Teacher Remarks',
                'name' => 'remarks',
            ]
        ]);
//
        $this->crud->addFields([
            [
                'name' => 'student_id',
                'label' => "Student Name",
                'type' => 'select_from_array',
                'options' => User::getAdminStudents(),
                'allows_null' => false,
            ],
            [
                'name' => 'exam_id',
                'label' => "Exam",
                'type' => 'select',
                'entity' => 'exam',
                'attribute' => 'date',
            ],
            [
                'label' => 'Total Marks',
                'name' => 'total_marks',
                'type' => 'text'
            ],
            [
                'label' => 'Obtained Marks',
                'name' => 'obtained_marks',
                'type' => 'text'
            ],
            [
                'label' => 'Teacher Remarks',
                'name' => 'remarks',
                'text' => 'text'
            ]
        ]);

        // add asterisk for fields that are required in ResultRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
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
}
