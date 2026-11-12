import { createContext, useCallback, useContext, useState } from 'react';
import { CheckCircle2, AlertCircle } from 'lucide-react';

const ToastCtx = createContext(() => {});
export const useToast = () => useContext(ToastCtx);

export function ToastProvider({ children }) {
  const [items, setItems] = useState([]);
  const push = useCallback((message, type = 'success') => {
    const id = Date.now() + Math.random();
    setItems((t) => [...t, { id, message, type }]);
    setTimeout(() => setItems((t) => t.filter((x) => x.id !== id)), 4500);
  }, []);
  return (
    <ToastCtx.Provider value={push}>
      {children}
      <div className="fixed bottom-5 right-5 z-[60] flex flex-col gap-2" role="status" aria-live="polite">
        {items.map((t) => (
          <div key={t.id} className="flex max-w-sm items-center gap-3 rounded-2xl bg-forest px-4 py-3 text-white shadow-soft">
            {t.type === 'error' ? <AlertCircle className="text-gold" size={20} /> : <CheckCircle2 className="text-gold" size={20} />}
            <span className="text-sm">{t.message}</span>
          </div>
        ))}
      </div>
    </ToastCtx.Provider>
  );
}
