use App\Http\Controllers\RoomController;

Route::get('/', [RoomController::class, 'showLoginForm'])->name('room.login');
Route::post('/join', [RoomController::class, 'joinRoom'])->name('room.join');
Route::get('/chat', [RoomController::class, 'chat'])->name('room.chat'); 