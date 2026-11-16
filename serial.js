let port;
async function connectArduino() {
  try {
    port = await navigator.serial.requestPort();
    await port.open({ baudRate: 9600 });
  } catch (err) {
    console.error('فشل الاتصال', err);
  }
}

async function sendSignal(signal) {
  if (port && port.writable) {
    const writer = port.writable.getWriter();
    const data = new TextEncoder().encode(signal);
    await writer.write(data);
    writer.releaseLock();
  }
}
