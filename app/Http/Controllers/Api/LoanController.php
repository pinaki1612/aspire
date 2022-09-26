<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanApproveRequest;
use App\Http\Requests\LoanRepaymentRequest;
use App\Http\Requests\LoanStoreRequest;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\ScheduledRepayments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function store(LoanStoreRequest $request)
    {
        $user = getApiUser();
        DB::beginTransaction();
        try {
            $date=Carbon::now()->format('Ymd');
            $getSeqNo=Loan::selectRaw('MAX(CAST(SUBSTRING(loan_no, 10, 3) AS unsigned)) AS maxuser')
                ->where('loan_no','like',"L".$date."%")
                ->get()
                ->first();

            if($getSeqNo->maxuser == NULL){
                $loan_no='L'.$date."001";
            }elseif($getSeqNo->maxuser<9){
                $loan_no='L'.$date."00".++$getSeqNo->maxuser;
            }elseif($getSeqNo->maxuser<99){
                $loan_no='L'.$date."0".++$getSeqNo->maxuser;
            }else{
                $loan_no='L'.$date.++$getSeqNo->maxuser;
            }
            $loan             = new Loan();
            $loan->loan_no    = $loan_no;
            $loan->user_id = $user->id;
            $loan->loan_amount = $request['loan_amount'];
            //$loan->term_period = $request['term'];
            $loan->term = $request['term'];
            $loan->loan_status = 'PENDING';
            $loan->loan_created_date = now();
            $loan->save();

            $schedule = getLoanSchedule($request['loan_amount'],$request['term']);
            //dd($schedule);

            foreach ($schedule as $item){
                $scheduleRepayment             = new ScheduledRepayments();
                $scheduleRepayment->loan_id  = $loan->id;
                $scheduleRepayment->schedule_date  = $item['payment_date'];
                $scheduleRepayment->schedule_amount  = $item['payment_amount'];
                $scheduleRepayment->save();
            }
            DB::commit();
            $loan = Loan::find($loan->id);
            $loan['schedule_repayment'] = ScheduledRepayments::where("loan_id","=",$loan->id)->get();
            $data = ['loan'=>$loan];
            $msg = "Loan created successfully";
            $status = 'success';
            $httpStatus = Response::HTTP_OK;
        }catch (\Exception $e) {
            DB::rollback();
            $data = $e;
            $msg = "Loan failed to create";
            $status = 'error';
            $httpStatus = Response::HTTP_BAD_REQUEST;
        }

        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $msg
        ],$httpStatus);
    }

    public function list(Request $request)
    {
        $search = 1;
        if(!empty($request["search"])){
            $search = "(loans.id = '".$request["search"]."' or loans.loan_no like '%".$request["search"]."%')";
        }
        if(Auth::user()->hasRole('super-admin'))
        {
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')
                ->whereRaw($search)
                ->paginate(20);
        }else{
            $user = getApiUser();
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')
                ->where('user_id','=',$user->id)
                ->whereRaw($search)
                ->paginate(20);
        }

        return response()->json([
            'status' => 'success',
            'data' => ['loan'=>$loan],
            'message' => 'loan list'
        ],Response::HTTP_OK);

    }

    public function show(Request $request)
    {
        if(empty($request['loan_id']) || !is_numeric($request['loan_id']) || $request['loan_id']<=0){
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'loan id missing or invalid'
            ],Response::HTTP_BAD_REQUEST);
        }

        if(Auth::user()->hasRole('super-admin'))
        {
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')
                ->where('id','=',$request['loan_id'])
                ->get()->first();
        }else{
            $user = getApiUser();
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')
                ->where('user_id','=',$user->id)
                ->where('id','=',$request['loan_id'])
                ->get()->first();
        }

        return response()->json([
            'status' => 'success',
            'data' => ['loan'=>$loan],
            'message' => 'loan list'
        ],Response::HTTP_OK);
    }

    public function approve(LoanApproveRequest $request)
    {
        if(empty($request['loan_id']) || !is_numeric($request['loan_id']) || $request['loan_id']<=0){
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'loan id missing or invalid'
            ],Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = getApiUser();
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')->find($request['loan_id']);

            if(!empty($loan->id) && $loan->loan_status != 'PENDING'){
                $status = 'error';
                $data = ['loan'=>$loan];
                $message = 'Loan status should be in PENDING ';
                $httpStatus = Response::HTTP_BAD_REQUEST;
            }else{
                $loan->loan_status = $request['status'];
                $loan->loan_approved_by = $user->id;
                $loan->loan_approved_date = Carbon::now()->format('Y-m-d');
                $loan->save();

                $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')->find($request['loan_id']);

                $status = 'success';
                $data = ['loan'=>$loan];
                $message = 'loan status changed to '.$request['status'];
                $httpStatus = Response::HTTP_OK;
            }

        }catch (\Exception $e) {
            $status = 'error';
            $data = $e;
            $message = 'error in database';
            $httpStatus = Response::HTTP_BAD_REQUEST;
        }
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message
        ],$httpStatus);
    }

    public function repayment(LoanRepaymentRequest $request)
    {
        $user = getApiUser();
        $paymentLog = false;
        $loan = Loan::where('id','=',$request['loan_id'])
            ->where('user_id','=',$user->id)
            ->get()->first();

        if(empty($loan)){
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'loan id missing or invalid'
            ],Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            if ($loan->loan_status == 'APPROVED') {
                $loanAmount = $loan->loan_amount;
                $paidAmount = Payment::where('loan_id', '=', $request['loan_id'])->sum('amount');
                $scheduleRepaymentRemaining = ScheduledRepayments::where('loan_id', '=', $request['loan_id'])
                    ->where('status', '=', 'PENDING')
                    ->orderBy('id', 'asc')
                    ->get();

                $remainingAmount = $loanAmount - $paidAmount;
             if($remainingAmount <= 0){
                    $status = 'error';
                    $message = 'Loan already paid';
                    $httpStatus = Response::HTTP_BAD_REQUEST;
                }else if ($scheduleRepaymentRemaining[0]->schedule_amount > $request['amount'] && $remainingAmount > $scheduleRepaymentRemaining[0]->schedule_amount) {
                    // if repayment amount less than schedule amount
                    $status = 'error';
                    $message = 'Loan amount should be ' . $scheduleRepaymentRemaining[0]->schedule_amount;
                    $httpStatus = Response::HTTP_BAD_REQUEST;

                } else if ($remainingAmount == $request['amount']) {
                    // make all remaining schedule repayment to paid and loan to paid
                    foreach ($scheduleRepaymentRemaining as $item) {
                        $scheduledRepayments = ScheduledRepayments::find($item->id);
                        $scheduledRepayments->status = 'PAID';
                        $scheduledRepayments->save();
                    }
                    // update loan
                    $loan->loan_status = 'PAID';
                    $loan->save();

                    //set paymentLog true to update payment log
                    $paymentLog = true;

                    $status = 'success';
                    $message = 'Loan paid';
                    $httpStatus = Response::HTTP_OK;

                } else if ($remainingAmount > $request['amount']) {
                    // check number of schedule repayment can be to set to paid
                    $balenceAmount = $request['amount'];
                    foreach ($scheduleRepaymentRemaining as $item) {
                        if ($balenceAmount < $item->schedule_amount) {
                            break;
                        }

                        $scheduledRepayments = ScheduledRepayments::find($item->id);
                        $scheduledRepayments->status = 'PAID';
                        $scheduledRepayments->save();
                        $balenceAmount = $balenceAmount - $item->schedule_amount;

                        //set paymentLog true to update payment log
                        $paymentLog = true;

                        $status = 'success';
                        $message = 'Loan installment paid';
                        $httpStatus = Response::HTTP_OK;
                    }

                } else {
                    //return response that user had paid more than total loan amount
                    $status = 'error';
                    $message = 'Loan amount paid in excess. Loan amount should be ' . $remainingAmount;
                    $httpStatus = Response::HTTP_BAD_REQUEST;
                }

                if ($paymentLog) {
                    // log payment made to loan
                    $payment = new Payment();
                    $payment->loan_id = $loan->id;
                    $payment->amount = $request['amount'];
                    $payment->date = Carbon::now()->format('Y-m-d');
                    $payment->save();
                }
                DB::commit();
            } else {
                switch ($loan->loan_status) {
                    case 'PENDING':
                        $message = 'Loan need to be approved';
                        break;

                    case 'REJECTED':
                        $message = 'Loan is rejected';
                        break;

                    case 'PAID':
                        $message = 'Loan is paid';
                        break;

                    default:
                        $message = 'Loan status not belong to [APPROVED,PENDING,PAID,REJECTED]';
                }
                $status = 'error';
                $httpStatus = Response::HTTP_BAD_REQUEST;
            }
            $loan = Loan::with('customer')->with('approver')->with('scheduleRepayments')->with('payments')->find($request['loan_id']);
            $data = ['loan'=>$loan];

        }catch (\Exception $e) {
            $data = $e;
            $message = "Loan repayment failed";
            $status = 'error';
            $httpStatus = Response::HTTP_BAD_REQUEST;
        }
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message
        ],$httpStatus);

    }
}
