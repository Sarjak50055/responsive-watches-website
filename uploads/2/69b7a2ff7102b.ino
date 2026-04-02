// C++ code
//
void setup()
{
  pinMode(0, INPUT);
  pinMode(2, OUTPUT);
  pinMode(1, INPUT);
  pinMode(3, OUTPUT);
}

void loop()
{
  if (digitalRead(0) == HIGH) {
    digitalWrite(2, LOW);
  } else {
    digitalWrite(2, HIGH);
  }
  if (digitalRead(1) == HIGH) {
    digitalWrite(3, LOW);
  } else {
    digitalWrite(3, HIGH);
  }
  delay(10); // Delay a little bit to improve simulation performance
}