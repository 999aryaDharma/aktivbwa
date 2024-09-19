<?php

namespace App\Repositories\Contracts;

interface BookingRepositoryInterface
{
  public function createBooking(array $data);

  public function findByTrxIdAndPhoneNumber($bookingTrxId, $phoneNumber);

  public function saveToSession(array $data);

  public function updateSessiondata(array $data);

  public function getOrderdataFromSession();

  public function clearSession();
}