<?php
// |KB Контроллер для управления привязанными картами

namespace App\Http\Controllers;

use App\Models\UserCard;
use App\Services\TwoCanCardService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CardController extends Controller
{
    protected TwoCanCardService $cardService;

    public function __construct(TwoCanCardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Отображение формы привязки карты
     */
    public function showAttachForm(): View
    {
        return view('client.cards.attach');
    }

    /**
     * Привязка новой карты
     */
    public function attachCard(Request $request): RedirectResponse
    {
        // Basic validation
        $request->validate([
            'number' => 'required|string|regex:/^[0-9\s]{16,19}$/',
            'expiry' => 'required|string|regex:/^[0-9]{2}\/[0-9]{2}$/',
            'cvv' => 'required|string|regex:/^[0-9]{3,4}$/',
            'holder' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $cardData = $request->only(['number', 'expiry', 'cvv', 'holder']);

        try {
            $result = $this->cardService->tokenizeCard($user, $cardData);

            if ($result['success']) {
                return redirect()->route('client.cards.index')
                    ->with('success', $result['message']);
            } else {
                return back()->with('error', $result['error']);
            }
        } catch (\InvalidArgumentException $e) {
            // Validation errors from service
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Произошла ошибка при привязке карты');
        }
    }

    /**
     * Список привязанных карт
     */
    public function index(): View
    {
        $user = auth()->user();
        $cards = $this->cardService->getUserCards($user);

        return view('client.cards.index', compact('cards'));
    }

    /**
     * Установка карты по умолчанию
     */
    public function setDefault(Request $request, int $cardId): RedirectResponse
    {
        $user = auth()->user();

        if ($this->cardService->setDefaultCard($user, $cardId)) {
            return back()->with('success', 'Карта установлена по умолчанию');
        }

        return back()->with('error', 'Не удалось установить карту по умолчанию');
    }

    /**
     * Удаление привязанной карты
     */
    public function delete(int $cardId): RedirectResponse
    {
        $user = auth()->user();

        if ($this->cardService->deleteCard($user, $cardId)) {
            return back()->with('success', 'Карта успешно удалена');
        }

        return back()->with('error', 'Не удалось удалить карту');
    }
}