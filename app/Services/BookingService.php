<?php

namespace App\Services;

use App\Services\BookingService;
use Exception;
use App\Models\BookingTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\WorkshopRepositoryInterface;



class BookingsService
{
  protected $bookingRepository;
  protected $workshopRepository;
  public function __construct(WorkshopRepositoryInterface $workshopRepository, BookingRepositoryInterface $bookingRepository)
  {
    $this->bookingRepository = $bookingRepository;
    $this->workshopRepository = $workshopRepository;
  }

  public function storeBooking(array $validatedata)
  {
    $existingData = $this->bookingRepository->getOrderDataFromSession();
    $updateData = array_merge($existingData, $validatedata);
    $this->bookingRepository->saveToSession($updateData);
    return $updateData;
  }

  public function isBookingSessionAvailable()
  {
    return $this->bookingRepository->getOrderDataFromSession() !== null;
  }

  public function getBookingDetails()
  {
    $orderData = $this->bookingRepository->getOrderDataFromSession();

    if (empty($orderData)) {
      return null;
    }

    $workshop = $this->workshopRepository->find($orderData['workshop_id']);

    $quantity = isset($orderData['quantity']) ? $orderData['quantity'] : 1;
    $subTotalAmount = $workshop->price * $quantity;

    $taxRate = 0.11;
    $totalTax = $subTotalAmount * $taxRate;

    $orderData['sub_total_amount'] = $subTotalAmount;
    $orderData['total_tax'] = $totalTax;
    $orderData['total_amount'] = $totalAmount;

    $this->bookingRepository->saveToSession($orderData);

    return compact('orderData', 'workshop');
  }

  public function finalizeBookingPayment(array $paymentData)
  {
    $orderData = $this->bookingRepository->getOrderdataFromSession();
    if(!$orderData){
      throw new Exception('Booking data is missing from session.');
    }

    Log::info('Order Data:', $orderData); // log data

    if (!isset($orderData['total-amount'])) {
      throw new \Exception('Total amount is missing from order data.');
    }

    if (isset($paymentData['proof'])) {
      $proofPath = $paymentData['proof']->store('proofs', 'public');
    }
    
    DB::beginTransaction();
    try {
      $bookingTransaction = BookingTransaction::create([
        'name' => $orderData['name'],
        'email' => $orderData['email'],
        'phone' => $orderData['phone'],
        'customer_bank_name' => $orderData['customer_bank_name'],
        'customer_bank_number' => $orderData['customer_bank_number'],
        'customer_bank_account' => $orderData['customer_bank_account'],
        'proof' => $proofPath,
        'quantity' => $orderData['quantity'],
        'total_amount' => $orderData['total_amount'],
        'is_paid' => false,
        'workshop_id' => $orderData['workshop_id'],
        'booking_trx_id' => BookingTransaction::generateUniqueTrxId(),
      ]);

      foreach ($orderData['participants'] as $participant) {
        WorkshopParticipant::create([
          'name' => $participant['name'],
          'occupation' => $participant['occupation'],
          'email' => $participant['email'],
          'workshop_id' => $bookingTransaction->workshop_id,
          'booking_transaction_id' => $bookingTransaction->id,
        ]);  
      }

      DB::commit();

      $this->bookingRepository->clearSession();

      return $bookingTransaction->id; // Return the booking transaction ID for redirect
    
    } catch (\Exception $e) {
        // Log the exception for debugging
        Log::error('Payment processing failed: ' . $e->getMessage());

        // Rollbcak the transaction in case of any errors
        DB::rollback();

        // Rethrow the exception to be handled by the controller
        throw $e;
    }
  }

  public function getMyBookingDetails(array $validated)
  {
    return $this->bookingRepository->findByTrxIdAndPhoneNumber($validated['booking_trx_id'], $validated['phone']);
  }
}